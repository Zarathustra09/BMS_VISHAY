<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Machine;
use App\Models\MachineParameter;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MachineParameterReadingTest extends TestCase
{
    use RefreshDatabase;

    private Machine $machine;

    private string $deviceKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->machine = Machine::factory()->create();
        $this->deviceKey = $this->machine->generateDeviceKey();

        MachineParameter::factory()->for($this->machine)->key('coolant_temp', 'Coolant temperature', '°C')
            ->range(-20, 150)->create(['sort_order' => 0]);
        MachineParameter::factory()->for($this->machine)->key('door_open')->boolean()->create(['sort_order' => 1]);
        MachineParameter::factory()->for($this->machine)->key('mode')->text()->create(['sort_order' => 2]);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function postReading(array $body): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/machine-logs', $body, ['X-Device-Key' => $this->deviceKey]);
    }

    public function test_a_valid_reading_is_stored_per_parameter_with_its_type_cast(): void
    {
        $this->postReading([
            'log_type' => 'sensor_reading',
            'readings' => ['coolant_temp' => '42.5', 'door_open' => 1, 'mode' => 'eco'],
        ])->assertCreated();

        $this->assertSame(
            ['coolant_temp' => 42.5, 'door_open' => true, 'mode' => 'eco'],
            $this->machine->logs()->first()->readings
        );
    }

    public function test_a_null_reading_is_dropped_rather_than_stored(): void
    {
        $this->postReading([
            'log_type' => 'sensor_reading',
            'readings' => ['coolant_temp' => 30, 'mode' => null],
        ])->assertCreated();

        // assertEquals: JSON stores 30.0 as 30, so only the value (not int/float type) round-trips.
        $this->assertEquals(['coolant_temp' => 30], $this->machine->logs()->first()->readings);
    }

    public function test_an_unknown_parameter_key_is_rejected_and_the_accepted_keys_are_listed(): void
    {
        $response = $this->postReading([
            'log_type' => 'sensor_reading',
            'readings' => ['coolant_temp' => 30, 'tempreature' => 31],
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('readings');
        $this->assertStringContainsString('tempreature', $response->json('errors.readings.0'));
        $this->assertStringContainsString('coolant_temp, door_open, mode', $response->json('errors.readings.0'));
        $this->assertDatabaseCount('machine_logs', 0);
    }

    public function test_a_sensor_value_sent_the_old_top_level_way_is_rejected_not_silently_dropped(): void
    {
        $response = $this->postReading(['log_type' => 'sensor_reading', 'temperature' => 22.5]);

        $response->assertUnprocessable()->assertJsonValidationErrors('readings');
        $this->assertStringContainsString('temperature', $response->json('errors.readings.0'));
        $this->assertDatabaseCount('machine_logs', 0);
    }

    public function test_a_reading_of_the_wrong_type_is_rejected(): void
    {
        $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['coolant_temp' => 'hot']])
            ->assertJsonValidationErrors('readings.coolant_temp');

        $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['door_open' => 'maybe']])
            ->assertJsonValidationErrors('readings.door_open');

        $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['mode' => 12]])
            ->assertJsonValidationErrors('readings.mode');

        $this->assertDatabaseCount('machine_logs', 0);
    }

    public function test_a_number_outside_the_parameters_accepted_range_is_rejected(): void
    {
        $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['coolant_temp' => 151]])
            ->assertJsonValidationErrors('readings.coolant_temp');

        $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['coolant_temp' => -20.5]])
            ->assertJsonValidationErrors('readings.coolant_temp');

        $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['coolant_temp' => 150]])
            ->assertCreated();
    }

    public function test_a_required_parameter_must_be_present_in_a_sensor_reading(): void
    {
        $this->machine->parameters()->where('key', 'mode')->update(['required' => true]);

        $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['coolant_temp' => 30]])
            ->assertJsonValidationErrors('readings.mode');

        $this->postReading(['log_type' => 'sensor_reading'])
            ->assertJsonValidationErrors('readings.mode');

        $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['mode' => 'eco']])
            ->assertCreated();
    }

    public function test_a_required_parameter_is_not_enforced_on_other_log_types(): void
    {
        $this->machine->parameters()->where('key', 'mode')->update(['required' => true]);

        $this->postReading(['log_type' => 'alarm', 'event' => 'Door ajar', 'severity' => 'warning'])
            ->assertCreated();
    }

    public function test_a_machine_without_parameters_rejects_any_reading(): void
    {
        $bare = Machine::factory()->create();

        $this->postJson('/api/machine-logs', [
            'log_type' => 'sensor_reading',
            'readings' => ['temperature' => 22],
        ], ['X-Device-Key' => $bare->generateDeviceKey()])
            ->assertJsonValidationErrors('readings');
    }

    public function test_a_device_cannot_report_another_machines_parameter(): void
    {
        $other = Machine::factory()->create();
        MachineParameter::factory()->for($other)->key('flow_rate')->create();

        $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['flow_rate' => 5]])
            ->assertJsonValidationErrors('readings');
    }

    public function test_a_breaching_value_on_a_dynamic_parameter_raises_an_alert_with_its_label_and_unit(): void
    {
        Notification::fake();

        $this->machine->parameters()->where('key', 'coolant_temp')->update(['critical_max' => 100, 'warning_max' => 80]);

        $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['coolant_temp' => 120]])->assertCreated();

        $alert = Alert::where('machine_id', $this->machine->id)->sole();

        $this->assertSame('coolant_temp', $alert->metric);
        $this->assertSame('critical', $alert->severity);
        $this->assertStringContainsString('Coolant temperature', $alert->message);
        $this->assertStringContainsString('120 °C', $alert->message);
    }

    public function test_a_warning_bound_raises_a_warning_and_a_normal_reading_resolves_it(): void
    {
        Notification::fake();

        $this->machine->parameters()->where('key', 'coolant_temp')->update(['critical_max' => 100, 'warning_max' => 80]);

        $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['coolant_temp' => 90]])->assertCreated();
        $this->assertSame('warning', Alert::where('machine_id', $this->machine->id)->sole()->severity);

        $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['coolant_temp' => 60]])->assertCreated();
        $this->assertSame('resolved', Alert::where('machine_id', $this->machine->id)->sole()->status);
    }

    public function test_a_non_number_parameter_never_raises_a_threshold_alert(): void
    {
        $this->machine->parameters()->where('key', 'mode')->update(['critical_max' => 1]);

        $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['mode' => 'eco']])->assertCreated();

        $this->assertDatabaseCount('alerts', 0);
    }

    public function test_the_sensor_chart_series_comes_from_the_machines_own_parameters(): void
    {
        $user = User::factory()->create();
        $user->assignRole('manager');

        // Same timestamp, so both readings land in one chart bucket whatever the clock does.
        $at = now()->subMinutes(5)->toDateTimeString();
        $this->postReading(['log_type' => 'sensor_reading', 'logged_at' => $at, 'readings' => ['coolant_temp' => 40]])->assertCreated();
        $this->postReading(['log_type' => 'sensor_reading', 'logged_at' => $at, 'readings' => ['coolant_temp' => 50]])->assertCreated();

        $response = $this->actingAs($user)->getJson(route('machines.readings', ['machine' => $this->machine, 'range' => '1h']));

        $response->assertOk()
            ->assertJsonPath('metric', 'coolant_temp')
            ->assertJsonPath('label', 'Coolant temperature (°C)')
            ->assertJsonPath('available_metrics', [['value' => 'coolant_temp', 'label' => 'Coolant temperature (°C)']])
            ->assertJsonPath('latest', 45);

        // Only a number parameter can be charted, and an unknown key falls back to the first one.
        $this->actingAs($user)->getJson(route('machines.readings', ['machine' => $this->machine, 'metric' => 'door_open']))
            ->assertOk()
            ->assertJsonPath('metric', 'coolant_temp');
    }
}
