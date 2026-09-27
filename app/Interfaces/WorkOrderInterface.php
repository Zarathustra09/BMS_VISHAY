<?php

namespace App\Interfaces;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;

interface WorkOrderInterface
{
    /**
     * Query for the work-orders DataTable — every work order, optionally filtered by
     * status, priority, type, and machine.
     */
    public function getQueryForDataTable(array $filters): Builder;

    public function getStats(): array;

    /**
     * Creates the work order, generating its display code and stamping created_by.
     * Notifies the assignee (if one is set) — see WorkOrderAssignedNotification. $creator is
     * null for a system-generated work order (e.g. app:generate-preventive-work-orders),
     * which leaves created_by null rather than needing a synthetic user.
     */
    public function create(array $data, ?User $creator): WorkOrder;

    /**
     * Updates the work order's editable fields. Notifies the assignee only when
     * assigned_to actually changed to a new, non-null user (a reassignment).
     */
    public function update(WorkOrder $workOrder, array $data): WorkOrder;

    public function delete(WorkOrder $workOrder): bool;

    /**
     * Transitions status, stamping started_at/completed_at as appropriate. A technician
     * (a user without work-orders.update) may only update a work order assigned to them —
     * violating this throws WorkOrderActionException.
     *
     * Completing a work order resolves its linked alert (if any) via AlertInterface, and
     * — if it's a 'preventive' work order — advances the machine's last/next maintenance
     * dates by its configured maintenance_interval_days.
     */
    public function updateStatus(WorkOrder $workOrder, string $status, User $actor, ?string $resolutionNotes = null): WorkOrder;

    /**
     * Creates a 'preventive' work order (system-generated, created_by null) for every
     * machine whose next_maintenance_date falls within $leadDays, skipping machines that
     * are 'inactive' or already have an open preventive work order — see
     * app:generate-preventive-work-orders.
     *
     * @return array{checked:int, created:int, machines:array<int,string>}
     */
    public function generatePreventiveWorkOrders(int $leadDays): array;
}
