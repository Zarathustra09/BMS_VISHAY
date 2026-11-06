<?php

namespace Tests\Feature;

use App\Interfaces\AlertInterface;
use App\Models\Alert;
use App\Models\Machine;
use App\Models\MachineParameter;
use App\Models\User;
use App\Notifications\MachineAlertNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * A sensor sitting on a limit that is sampled many times a second must raise one alert (and send
 * one e-mail), not raise, clear and re-raise on every other reading.
 */
class AlertFlapGuardTest extends TestCase
{
    use RefreshDatabase;

    private Machine $machine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->travelTo(Carbon::parse('2026-10-12 12:00:00'));

        $this->machine = Machine::factory()->create();
        $this->machine->generateDeviceKey('flap-key');
        MachineParameter::factory()->for($this->machine)->key('temperature')
            ->withThresholds(['warning_max' => 80, 'critical_max' => 100])->create();

        tap(User::factory()->create())->assignRole('admin');
    }

    private function reading(float $temperature): TestResponse
    {
        return $this->postJson('/api/machine-logs', [
            'log_type' => 'sensor_reading',
            'readings' => ['temperature' => $temperature],
        ], ['X-Device-Key' => 'flap-key'])->assertCreated();
    }

    public function test_a_breach_raises_at_once_with_the_reading_that_caused_it(): void
    {
        Notification::fake();

        $this->reading(95);

        $alert = Alert::sole();
        $this->assertSame('warning', $alert->severity);
        $this->assertEquals(95, $alert->value);
        $this->assertNotNull($alert->last_breach_at);
    }

    public function test_a_normal_reading_right_after_a_breach_does_not_resolve_it(): void
    {
        $this->reading(95);
        $this->travel(1)->second();
        $this->reading(60);

        $this->assertSame('open', Alert::sole()->status);
    }

    public function test_readings_that_stay_normal_resolve_it_after_the_grace_period(): void
    {
        $this->reading(95);
        $this->travel(AlertInterface::RESOLVE_AFTER_NORMAL_SECONDS)->seconds();
        $this->reading(60);

        $alert = Alert::sole();
        $this->assertSame('resolved', $alert->status);
        $this->assertSame('auto', $alert->resolution);
    }

    public function test_a_sensor_flapping_across_the_limit_makes_one_alert_and_one_notification(): void
    {
        Notification::fake();

        foreach ([95, 60, 95, 60, 95, 60] as $value) {
            $this->reading($value);
            $this->travel(1)->second();
        }

        $this->assertSame(1, Alert::count());
        $this->assertSame('open', Alert::sole()->status);

        Notification::assertSentTimes(MachineAlertNotification::class, 1);
    }

    public function test_each_breach_pushes_the_grace_period_forward(): void
    {
        $this->reading(95);
        $this->travel(4)->seconds();
        $this->reading(96);        // still breaching, 4 s in
        $this->travel(4)->seconds();
        $this->reading(60);        // 4 s after the LAST breach: not yet

        $this->assertSame('open', Alert::sole()->status);

        $this->travel(2)->seconds();
        $this->reading(60);        // 6 s after it

        $this->assertSame('resolved', Alert::sole()->status);
    }
}
