<?php

namespace Tests\Feature;

use App\Interfaces\MachineLogInterface;
use App\Models\Machine;
use App\Models\MachineParameter;
use App\Models\User;
use App\Notifications\MachineAlertNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ThresholdAlertNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_a_reading_past_the_critical_threshold_raises_an_alert_and_notifies_admins(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $machine = Machine::factory()->create();
        MachineParameter::factory()->for($machine)->key('temperature')->withThresholds(['critical_max' => 40])->create();

        app(MachineLogInterface::class)->store([
            'machine_id' => $machine->id,
            'log_type' => 'sensor_reading',
            'readings' => ['temperature' => 45],
        ]);

        $this->assertDatabaseHas('alerts', [
            'machine_id' => $machine->id,
            'metric' => 'temperature',
            'status' => 'open',
            'severity' => 'critical',
        ]);

        Notification::assertSentTo($admin, MachineAlertNotification::class);
    }

    public function test_a_second_breaching_reading_does_not_raise_a_duplicate_alert(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $machine = Machine::factory()->create();
        MachineParameter::factory()->for($machine)->key('temperature')->withThresholds(['critical_max' => 40])->create();

        $logService = app(MachineLogInterface::class);
        $logService->store(['machine_id' => $machine->id, 'log_type' => 'sensor_reading', 'readings' => ['temperature' => 45]]);
        $logService->store(['machine_id' => $machine->id, 'log_type' => 'sensor_reading', 'readings' => ['temperature' => 46]]);

        $this->assertSame(
            1,
            \App\Models\Alert::where('machine_id', $machine->id)->where('type', 'threshold')->count()
        );

        Notification::assertSentToTimes($admin, MachineAlertNotification::class, 1);
    }

    public function test_a_reading_back_within_bounds_auto_resolves_the_alert(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $machine = Machine::factory()->create();
        MachineParameter::factory()->for($machine)->key('temperature')->withThresholds(['critical_max' => 40])->create();

        $logService = app(MachineLogInterface::class);
        $logService->store(['machine_id' => $machine->id, 'log_type' => 'sensor_reading', 'readings' => ['temperature' => 45]]);
        $logService->store(['machine_id' => $machine->id, 'log_type' => 'sensor_reading', 'readings' => ['temperature' => 30]]);

        $this->assertDatabaseHas('alerts', [
            'machine_id' => $machine->id,
            'metric' => 'temperature',
            'status' => 'resolved',
        ]);
    }
}
