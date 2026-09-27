<?php

namespace Tests\Feature;

use App\Interfaces\WorkOrderInterface;
use App\Models\Alert;
use App\Models\Machine;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_completing_an_alert_linked_work_order_resolves_the_alert(): void
    {
        $machine = Machine::factory()->create();
        $alert = Alert::create([
            'machine_id' => $machine->id,
            'type' => 'threshold',
            'metric' => 'temperature',
            'value' => 45,
            'severity' => 'critical',
            'status' => 'open',
            'message' => 'Temperature too high.',
        ]);

        $manager = User::factory()->create();
        $manager->assignRole('manager');

        $workOrders = app(WorkOrderInterface::class);
        $workOrder = $workOrders->create([
            'title' => 'Investigate overheating',
            'machine_id' => $machine->id,
            'alert_id' => $alert->id,
            'type' => 'corrective',
            'priority' => 'high',
        ], $manager);

        $workOrders->updateStatus($workOrder, 'completed', $manager, 'Replaced the sensor.');

        $this->assertSame('completed', $workOrder->fresh()->status);
        $this->assertNotNull($workOrder->fresh()->completed_at);
        $this->assertSame('resolved', $alert->fresh()->status);
    }

    public function test_completing_a_preventive_work_order_advances_the_machines_maintenance_dates(): void
    {
        $machine = Machine::factory()->create([
            'maintenance_interval_days' => 30,
            'next_maintenance_date' => now()->subDay(),
        ]);

        $technician = User::factory()->create();
        $technician->assignRole('technician');

        $workOrders = app(WorkOrderInterface::class);
        $workOrder = $workOrders->create([
            'title' => 'Preventive maintenance',
            'machine_id' => $machine->id,
            'type' => 'preventive',
            'priority' => 'medium',
            'assigned_to' => $technician->id,
        ], null);

        $workOrders->updateStatus($workOrder, 'completed', $technician);

        $machine->refresh();
        $this->assertNotNull($machine->last_maintenance_date);
        $this->assertTrue($machine->last_maintenance_date->isToday());
        $this->assertTrue($machine->next_maintenance_date->isSameDay(now()->addDays(30)));
    }

    public function test_a_technician_cannot_complete_a_work_order_assigned_to_someone_else(): void
    {
        $machine = Machine::factory()->create();
        $technician = User::factory()->create();
        $technician->assignRole('technician');
        $otherTechnician = User::factory()->create();
        $otherTechnician->assignRole('technician');

        $workOrders = app(WorkOrderInterface::class);
        $workOrder = $workOrders->create([
            'title' => 'Fix pump',
            'machine_id' => $machine->id,
            'type' => 'corrective',
            'priority' => 'low',
            'assigned_to' => $otherTechnician->id,
        ], null);

        $this->expectException(\App\Exceptions\WorkOrderActionException::class);

        $workOrders->updateStatus($workOrder, 'completed', $technician);
    }
}
