<?php

namespace App\Services;

use App\Exceptions\WorkOrderActionException;
use App\Interfaces\AlertInterface;
use App\Interfaces\WorkOrderInterface;
use App\Models\Machine;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\WorkOrderAssignedNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;

class WorkOrderService implements WorkOrderInterface
{
    public function __construct(protected AlertInterface $alerts) {}

    public function getQueryForDataTable(array $filters): Builder
    {
        $query = WorkOrder::with(['machine', 'assignedTo']);

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority']) && $filters['priority'] !== 'all') {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['machine_id']) && $filters['machine_id'] !== 'all') {
            $query->where('machine_id', $filters['machine_id']);
        }

        return $query->orderByDesc('created_at');
    }

    public function getStats(): array
    {
        return [
            'total' => WorkOrder::count(),
            'pending' => WorkOrder::where('status', 'pending')->count(),
            'in_progress' => WorkOrder::where('status', 'in_progress')->count(),
            'completed' => WorkOrder::where('status', 'completed')->count(),
            'urgent' => WorkOrder::where('priority', 'urgent')->open()->count(),
        ];
    }

    public function create(array $data, ?User $creator): WorkOrder
    {
        $data['code'] = $this->generateCode();
        $data['created_by'] = $creator?->id;
        $data['status'] = $data['status'] ?? 'pending';

        $workOrder = WorkOrder::create($data);

        if ($workOrder->assigned_to) {
            $this->notifyAssignee($workOrder);
        }

        return $workOrder;
    }

    public function update(WorkOrder $workOrder, array $data): WorkOrder
    {
        $previousAssignee = $workOrder->assigned_to;

        $workOrder->update($data);

        if ($workOrder->assigned_to && $workOrder->assigned_to !== $previousAssignee) {
            $this->notifyAssignee($workOrder->fresh());
        }

        return $workOrder->fresh();
    }

    public function delete(WorkOrder $workOrder): bool
    {
        return $workOrder->delete();
    }

    public function updateStatus(WorkOrder $workOrder, string $status, User $actor, ?string $resolutionNotes = null): WorkOrder
    {
        // Anyone with the broader work-orders.update permission (manager/admin) can update
        // any work order's status; anyone without it (technician) may only touch one
        // assigned to them.
        if (! $actor->can('work-orders.update') && $workOrder->assigned_to !== $actor->id) {
            throw new WorkOrderActionException('You can only update the status of work orders assigned to you.');
        }

        $update = ['status' => $status];

        if ($status === 'in_progress' && ! $workOrder->started_at) {
            $update['started_at'] = now();
        }

        if ($status === 'completed') {
            $update['completed_at'] = now();
            $update['resolution_notes'] = $resolutionNotes ?: $workOrder->resolution_notes;
        }

        $workOrder->update($update);

        if ($status === 'completed') {
            if ($workOrder->alert_id && $workOrder->alert) {
                $this->alerts->resolve($workOrder->alert, $actor, 'work_order');
            }

            if ($workOrder->type === 'preventive' && $workOrder->machine) {
                $intervalDays = $workOrder->machine->maintenance_interval_days ?? 90;

                $workOrder->machine->update([
                    'last_maintenance_date' => now(),
                    'next_maintenance_date' => now()->addDays($intervalDays),
                ]);
            }
        }

        return $workOrder->fresh();
    }

    public function generatePreventiveWorkOrders(int $leadDays): array
    {
        $cutoff = today()->addDays($leadDays);

        $machines = Machine::query()
            ->whereNotNull('next_maintenance_date')
            ->where('next_maintenance_date', '<=', $cutoff)
            ->where('status', '!=', 'inactive')
            ->whereDoesntHave('workOrders', function ($query) {
                $query->where('type', 'preventive')->open();
            })
            ->get();

        $created = [];

        foreach ($machines as $machine) {
            $overdue = $machine->next_maintenance_date->isPast();

            $this->create([
                'title' => "Preventive maintenance due — {$machine->name}",
                'description' => 'Automatically generated from the machine\'s maintenance schedule ('
                    .$machine->next_maintenance_date->format('M d, Y').').',
                'machine_id' => $machine->id,
                'type' => 'preventive',
                'priority' => $overdue ? 'high' : 'medium',
                'due_date' => $machine->next_maintenance_date,
            ], null);

            $created[$machine->id] = $machine->name;
        }

        return ['checked' => $machines->count(), 'created' => count($created), 'machines' => $created];
    }

    private function notifyAssignee(WorkOrder $workOrder): void
    {
        if ($assignee = $workOrder->assignedTo) {
            Notification::send($assignee, new WorkOrderAssignedNotification($workOrder));
        }
    }

    /**
     * WO-{year}-{4-digit sequence within that year}. Not concurrency-safe against a
     * simultaneous double-submit (no lock), but `code` has a DB-level unique constraint as
     * a backstop — acceptable at this app's scale, consistent with how machine_code
     * uniqueness is handled (validated, not locked).
     */
    private function generateCode(): string
    {
        $year = now()->year;
        $count = WorkOrder::withTrashed()->whereYear('created_at', $year)->count() + 1;

        return "WO-{$year}-".str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
