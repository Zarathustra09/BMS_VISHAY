<?php

namespace Tests\Feature;

use App\Interfaces\MachineLogInterface;
use App\Models\Machine;
use App\Models\MachineParameter;
use App\Models\MachineType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SensorParametersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function admin(): User
    {
        return tap(User::factory()->create())->assignRole('admin');
    }

    /** @param  array<string, mixed>  $limits */
    private function parameter(array $limits, array $attributes = []): MachineParameter
    {
        $machine = Machine::factory()->create();

        return MachineParameter::factory()->for($machine)->key('temperature', 'Temperature', '°C')
            ->create($limits + $attributes);
    }

    /** @param  array<string, mixed>  $row */
    private function save(Machine $machine, array $row): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->admin())->put(route('machines.update', $machine), [
            'name' => $machine->name,
            'machine_code' => $machine->machine_code,
            'machine_type_id' => MachineType::factory()->create()->id,
            'status' => 'active',
            'parameters' => [['label' => 'Temperature', 'data_type' => 'number', 'unit' => '°C'] + $row],
        ]);
    }

    // ---- the severity rule

    public function test_severity_follows_the_limits(): void
    {
        $p = $this->parameter(['warning_min' => 20, 'warning_max' => 80, 'critical_min' => 10, 'critical_max' => 100]);

        $this->assertNull($p->severityFor(50));
        $this->assertNull($p->severityFor(20));
        $this->assertSame('warning', $p->severityFor(85));
        $this->assertSame('warning', $p->severityFor(15));
        $this->assertSame('critical', $p->severityFor(101));
        $this->assertSame('critical', $p->severityFor(5));
    }

    public function test_a_parameter_with_no_limits_is_never_in_an_alert_state(): void
    {
        $this->assertNull($this->parameter([])->severityFor(9999));
    }

    public function test_limits_are_described_in_plain_words(): void
    {
        $p = $this->parameter(['warning_min' => 20, 'warning_max' => 80, 'critical_max' => 100]);

        $this->assertSame([
            'warning' => 'Warning below 20 or above 80 °C',
            'critical' => 'Critical above 100 °C',
        ], $p->limitLines());
        $this->assertSame([], $this->parameter([])->limitLines());
    }

    public function test_the_accepted_range_reads_naturally(): void
    {
        $this->assertSame('Any', $this->parameter([])->rangeText());
        $this->assertSame('-20 – 150', $this->parameter(['min' => -20, 'max' => 150])->rangeText());
        $this->assertSame('≥ 0', $this->parameter(['min' => 0])->rangeText());
        $this->assertSame('≤ 99.5', $this->parameter(['max' => 99.5])->rangeText());
    }

    public function test_the_classic_mistake_is_detected(): void
    {
        // "Warning 60-80, critical 81-100" entered as if they were ranges.
        $p = $this->parameter(['warning_min' => 60, 'warning_max' => 80, 'critical_min' => 81, 'critical_max' => 100]);

        $problems = $p->thresholdProblems();

        $this->assertCount(1, $problems);
        $this->assertStringContainsString('Critical below (81) is higher than Warning below (60)', $problems[0]);
        $this->assertStringContainsString('every reading under 81 is critical', $problems[0]);
        $this->assertSame('critical', $p->severityFor(50), 'the contradiction really does make a normal reading critical');
    }

    public function test_consistent_limits_have_no_problems(): void
    {
        $this->assertSame([], $this->parameter(['warning_min' => 20, 'warning_max' => 80, 'critical_min' => 10, 'critical_max' => 100])->thresholdProblems());
        $this->assertSame([], $this->parameter(['warning_max' => 80])->thresholdProblems());
        $this->assertSame([], $this->parameter([])->thresholdProblems());
    }

    public function test_critical_above_below_warning_above_is_a_problem(): void
    {
        $problems = $this->parameter(['warning_max' => 80, 'critical_max' => 70])->thresholdProblems();

        $this->assertCount(1, $problems);
        $this->assertStringContainsString('Critical above (70) is lower than Warning above (80)', $problems[0]);
    }

    // ---- saving validation

    public function test_saving_contradicting_limits_is_rejected_with_the_error_on_the_right_box(): void
    {
        $machine = Machine::factory()->create();

        $this->save($machine, ['warning_min' => 60, 'warning_max' => 80, 'critical_min' => 81, 'critical_max' => 100])
            ->assertSessionHasErrors(['parameters.0.critical_min']);

        $this->assertSame(0, $machine->parameters()->count());
    }

    public function test_saving_sensible_limits_succeeds(): void
    {
        $machine = Machine::factory()->create();

        $this->save($machine, ['warning_min' => 60, 'warning_max' => 80, 'critical_min' => 40, 'critical_max' => 100])
            ->assertSessionHasNoErrors();

        $p = $machine->parameters()->sole();
        $this->assertSame('critical', $p->severityFor(30));
        $this->assertNull($p->severityFor(70));
    }

    public function test_each_pair_of_limits_is_checked(): void
    {
        $machine = Machine::factory()->create();

        $this->save($machine, ['warning_min' => 90, 'warning_max' => 80])->assertSessionHasErrors('parameters.0.warning_max');
        $this->save($machine, ['critical_min' => 50, 'critical_max' => 40])->assertSessionHasErrors('parameters.0.critical_max');
        $this->save($machine, ['warning_max' => 80, 'critical_max' => 70])->assertSessionHasErrors('parameters.0.critical_max');
    }

    public function test_a_single_limit_is_always_acceptable(): void
    {
        $machine = Machine::factory()->create();

        $this->save($machine, ['critical_max' => 100])->assertSessionHasNoErrors();
        $this->assertSame(1, $machine->parameters()->count());
    }

    // ---- machine page

    private function machineWithReading(float $temperature, array $limits): Machine
    {
        $machine = Machine::factory()->create();
        MachineParameter::factory()->for($machine)->key('temperature', 'Temperature', '°C')->create($limits);
        MachineParameter::factory()->for($machine)->key('humidity', 'Humidity', '%')->create();

        app(MachineLogInterface::class)->store([
            'machine_id' => $machine->id,
            'log_type' => 'sensor_reading',
            'readings' => ['temperature' => $temperature, 'humidity' => 55],
        ]);

        return $machine;
    }

    public function test_the_overview_lists_each_parameter_with_its_latest_value_and_state(): void
    {
        $machine = $this->machineWithReading(105, ['warning_max' => 80, 'critical_max' => 100]);

        $this->actingAs($this->admin())->get(route('machines.show', $machine))
            ->assertOk()
            ->assertSee('Sensor parameters')
            ->assertSee('data-param-value="temperature"', false)
            ->assertSee('105 °C')
            ->assertSee('55 %')
            ->assertSee('Warning above 80 °C')
            ->assertSee('Critical above 100 °C')
            ->assertSee('Critical')
            ->assertSee('No limits')
            ->assertDontSee('These limits contradict each other');
    }

    public function test_the_overview_shows_normal_and_no_data_states(): void
    {
        $machine = Machine::factory()->create();
        MachineParameter::factory()->for($machine)->key('temperature', 'Temperature', '°C')->create(['warning_max' => 80]);

        $this->actingAs($this->admin())->get(route('machines.show', $machine))
            ->assertOk()
            ->assertSee('No data yet')
            ->assertSee('No data');

        app(MachineLogInterface::class)->store(['machine_id' => $machine->id, 'log_type' => 'sensor_reading', 'readings' => ['temperature' => 50]]);

        $this->actingAs($this->admin())->get(route('machines.show', $machine))->assertSee('Normal');
    }

    public function test_the_page_flags_contradicting_saved_limits_and_only_links_to_the_fix_for_editors(): void
    {
        $machine = $this->machineWithReading(50, ['warning_min' => 60, 'warning_max' => 80, 'critical_min' => 81, 'critical_max' => 100]);

        $this->actingAs($this->admin())->get(route('machines.show', $machine))
            ->assertOk()
            ->assertSee('These limits contradict each other')
            ->assertSee('Fix on the Edit page');

        $viewer = User::factory()->create();
        $viewer->assignRole(Role::create(['name' => 'machines-only'])->givePermissionTo('machines.view'));

        $this->actingAs($viewer)->get(route('machines.show', $machine))
            ->assertOk()
            ->assertSee('These limits contradict each other')
            ->assertDontSee('Fix on the Edit page')
            ->assertDontSee('Edit parameters');
    }

    public function test_a_machine_without_parameters_says_how_to_add_them(): void
    {
        $machine = Machine::factory()->create();

        $this->actingAs($this->admin())->get(route('machines.show', $machine))
            ->assertOk()
            ->assertSee('No sensor parameters yet')
            ->assertSee('Add them on the Edit page');
    }

    public function test_the_summary_endpoint_carries_each_parameters_live_state(): void
    {
        $machine = $this->machineWithReading(105, ['warning_max' => 80, 'critical_max' => 100]);

        $json = $this->actingAs($this->admin())->getJson(route('machines.summary', $machine))->assertOk()->json('parameters');

        $this->assertSame(['key' => 'temperature', 'display' => '105 °C', 'ago' => $json[0]['ago'], 'state' => 'critical'], $json[0]);
        $this->assertSame('no_limits', $json[1]['state']);
        $this->assertSame('55 %', $json[1]['display']);
    }
}
