<?php

namespace Tests\Feature;

use App\Interfaces\MachineLogInterface;
use App\Models\Alert;
use App\Models\Machine;
use App\Models\MachineParameter;
use App\Models\User;
use App\Notifications\MachineAlertNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AlertAcknowledgedBehaviourTest extends TestCase
{
    use RefreshDatabase;

    private Machine $machine;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = tap(User::factory()->create())->assignRole('admin');
        $this->machine = Machine::factory()->create();
        MachineParameter::factory()->for($this->machine)->key('temperature')
            ->withThresholds(['warning_max' => 30, 'critical_max' => 40])->create();
    }

    private function reading(float $temperature): void
    {
        app(MachineLogInterface::class)->store([
            'machine_id' => $this->machine->id,
            'log_type' => 'sensor_reading',
            'readings' => ['temperature' => $temperature],
        ]);
    }

    private function acknowledge(Alert $alert): void
    {
        $this->actingAs($this->admin)->post(route('alerts.acknowledge', $alert))->assertRedirect();
    }

    public function test_an_acknowledged_alert_auto_resolves_when_the_reading_recovers(): void
    {
        $this->reading(35);
        $alert = Alert::sole();
        $this->acknowledge($alert);

        $this->reading(22);

        $this->assertSame('resolved', $alert->fresh()->status);
        $this->assertSame('auto', $alert->fresh()->resolution);
    }

    public function test_a_new_breach_reuses_the_acknowledged_alert_instead_of_duplicating_it(): void
    {
        Notification::fake();
        $this->reading(35);
        $alert = Alert::sole();
        $this->acknowledge($alert);

        $this->reading(36);

        $this->assertSame(1, Alert::count());
        $this->assertSame('acknowledged', $alert->fresh()->status);
        $this->assertSame($this->admin->id, $alert->fresh()->acknowledged_by);
        Notification::assertSentToTimes($this->admin, MachineAlertNotification::class, 1);
    }

    public function test_getting_worse_after_acknowledging_reopens_the_alert_and_notifies_again(): void
    {
        Notification::fake();
        $this->reading(35);
        $alert = Alert::sole();
        $this->acknowledge($alert);

        $this->reading(45);

        $alert = $alert->fresh();
        $this->assertSame(1, Alert::count());
        $this->assertSame('open', $alert->status);
        $this->assertSame('critical', $alert->severity);
        $this->assertNull($alert->acknowledged_by);
        $this->assertSame('escalated', $alert->events()->first()->action);
        $this->assertSame('acknowledged', $alert->events()->first()->from_status);
        Notification::assertSentToTimes($this->admin, MachineAlertNotification::class, 2);
    }

    public function test_a_still_open_alert_that_stays_at_the_same_severity_is_not_renotified(): void
    {
        Notification::fake();
        $this->reading(35);
        $this->reading(36);
        $this->reading(37);

        Notification::assertSentToTimes($this->admin, MachineAlertNotification::class, 1);
        $this->assertSame(1, Alert::sole()->events()->count());
    }
}
