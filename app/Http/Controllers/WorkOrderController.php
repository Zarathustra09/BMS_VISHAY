<?php

namespace App\Http\Controllers;

use App\DataTables\WorkOrdersDataTable;
use App\Exceptions\WorkOrderActionException;
use App\Interfaces\WorkOrderInterface;
use App\Models\Alert;
use App\Models\Machine;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    public function __construct(protected WorkOrderInterface $workOrderService)
    {
        $this->middleware('permission:work-orders.view')->only(['index', 'show', 'data']);
        $this->middleware('permission:work-orders.create')->only(['create', 'store']);
        $this->middleware('permission:work-orders.update')->only(['edit', 'update']);
        $this->middleware('permission:work-orders.delete')->only(['destroy', 'confirmDelete']);
        $this->middleware('permission:work-orders.update-status')->only(['updateStatus']);
    }

    public function index()
    {
        $stats = $this->workOrderService->getStats();
        $machines = Machine::select('id', 'name')->orderBy('name')->get();

        return view('work_orders.index', compact('stats', 'machines'));
    }

    public function data(WorkOrdersDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function create(Request $request)
    {
        $machines = Machine::select('id', 'name')->orderBy('name')->get();
        $assignees = User::orderBy('name')->get();

        $alert = $request->filled('alert_id') ? Alert::find($request->integer('alert_id')) : null;
        $machineId = $request->integer('machine_id') ?: $alert?->machine_id;
        $type = in_array($request->query('type'), ['preventive', 'corrective', 'emergency', 'inspection'], true)
            ? $request->query('type')
            : 'corrective';

        return view('work_orders.create', compact('machines', 'assignees', 'machineId', 'alert', 'type'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateWorkOrder($request);

        $workOrder = $this->workOrderService->create($validated, $request->user());

        return redirect()->route('work-orders.show', $workOrder)
            ->with('success', 'Work order created successfully.');
    }

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load(['machine', 'alert', 'assignedTo', 'createdBy']);

        return view('work_orders.show', compact('workOrder'));
    }

    public function edit(WorkOrder $workOrder)
    {
        $machines = Machine::select('id', 'name')->orderBy('name')->get();
        $assignees = User::orderBy('name')->get();

        return view('work_orders.edit', compact('workOrder', 'machines', 'assignees'));
    }

    public function update(Request $request, WorkOrder $workOrder)
    {
        $validated = $this->validateWorkOrder($request);

        $this->workOrderService->update($workOrder, $validated);

        return redirect()->route('work-orders.show', $workOrder)
            ->with('success', 'Work order updated successfully.');
    }

    public function confirmDelete(WorkOrder $workOrder)
    {
        return view('work_orders.delete', compact('workOrder'));
    }

    public function destroy(WorkOrder $workOrder)
    {
        $this->workOrderService->delete($workOrder);

        return redirect()->route('work-orders.index')
            ->with('success', 'Work order deleted successfully.');
    }

    public function updateStatus(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'resolution_notes' => 'nullable|string|max:2000',
        ]);

        try {
            $this->workOrderService->updateStatus(
                $workOrder,
                $validated['status'],
                $request->user(),
                $validated['resolution_notes'] ?? null
            );
        } catch (WorkOrderActionException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Work order status updated.');
    }

    private function validateWorkOrder(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'machine_id' => 'required|exists:machines,id',
            'alert_id' => 'nullable|exists:alerts,id',
            'type' => 'required|in:preventive,corrective,emergency,inspection',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);
    }
}
