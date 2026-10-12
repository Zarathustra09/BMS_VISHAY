<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MachineParameter;
use App\Models\MachineType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineParameterBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function actingAsManager(): User
    {
        $manager = User::factory()->create();
        $manager->assignRole('manager');
        $this->actingAs($manager);

        return $manager;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function machinePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Press 7',
            'machine_code' => 'MC-7000',
            'machine_type_id' => MachineType::factory()->create()->id,
            'status' => 'active',
        ], $overrides);
    }

    public function test_creating_a_machine_persists_its_parameters_in_order_with_generated_keys(): void
    {
        $this->actingAsManager();

        $this->post(route('machines.store'), $this->machinePayload(['parameters' => [
            ['label' => 'Coolant Temp', 'data_type' => 'number', 'unit' => '°C', 'min' => '-20', 'max' => '150', 'critical_max' => '100'],
            ['label' => 'Door Open', 'data_type' => 'boolean', 'required' => '1'],
            ['label' => 'Coolant Temp', 'data_type' => 'text'],
        ]]))->assertRedirect();

        $parameters = Machine::firstOrFail()->parameters;

        $this->assertSame(['coolant_temp', 'door_open', 'coolant_temp_2'], $parameters->pluck('key')->all());
        $this->assertSame([0, 1, 2], $parameters->pluck('sort_order')->all());
        $this->assertSame('°C', $parameters[0]->unit);
        $this->assertSame(100.0, (float) $parameters[0]->critical_max);
        $this->assertTrue($parameters[1]->required);
    }

    public function test_threshold_and_range_bounds_are_ignored_for_a_non_number_parameter(): void
    {
        $this->actingAsManager();

        $this->post(route('machines.store'), $this->machinePayload(['parameters' => [
            ['label' => 'Mode', 'data_type' => 'text', 'min' => '1', 'critical_max' => '5'],
        ]]))->assertRedirect();

        $parameter = Machine::firstOrFail()->parameters->sole();

        $this->assertNull($parameter->min);
        $this->assertNull($parameter->critical_max);
    }

    public function test_updating_a_machine_keeps_a_saved_parameters_key_and_data_type_whatever_is_posted(): void
    {
        $this->actingAsManager();

        $machine = Machine::factory()->create();
        $parameter = MachineParameter::factory()->for($machine)->key('coolant_temp', 'Coolant temperature')->create();

        $this->put(route('machines.update', $machine), $this->machinePayload([
            'machine_type_id' => $machine->machine_type_id,
            'machine_code' => $machine->machine_code,
            'parameters' => [
                ['id' => $parameter->id, 'key' => 'renamed', 'label' => 'Renamed label', 'data_type' => 'text', 'unit' => 'K'],
            ],
        ]))->assertRedirect();

        $parameter->refresh();

        $this->assertSame('coolant_temp', $parameter->key);
        $this->assertSame('number', $parameter->data_type->value);
        $this->assertSame('Renamed label', $parameter->label);
        $this->assertSame('K', $parameter->unit);
    }

    public function test_a_row_left_out_of_the_form_is_deleted_and_its_key_can_be_reused(): void
    {
        $this->actingAsManager();

        $machine = Machine::factory()->create();
        $keep = MachineParameter::factory()->for($machine)->key('keep_me')->create(['sort_order' => 0]);
        MachineParameter::factory()->for($machine)->key('drop_me')->create(['sort_order' => 1]);

        $this->put(route('machines.update', $machine), $this->machinePayload([
            'machine_type_id' => $machine->machine_type_id,
            'machine_code' => $machine->machine_code,
            'parameters' => [
                ['label' => 'Drop Me', 'key' => 'drop_me', 'data_type' => 'boolean'],
                ['id' => $keep->id, 'label' => 'Keep me', 'data_type' => 'number'],
            ],
        ]))->assertRedirect();

        $parameters = $machine->parameters()->get();

        $this->assertSame(['drop_me', 'keep_me'], $parameters->pluck('key')->all());
        $this->assertSame('boolean', $parameters[0]->data_type->value);
    }

    public function test_submitting_no_parameters_removes_them_all(): void
    {
        $this->actingAsManager();

        $machine = Machine::factory()->create();
        MachineParameter::factory()->for($machine)->count(2)->create();

        $this->put(route('machines.update', $machine), $this->machinePayload([
            'machine_type_id' => $machine->machine_type_id,
            'machine_code' => $machine->machine_code,
        ]))->assertRedirect();

        $this->assertSame(0, $machine->parameters()->count());
    }

    public function test_a_parameter_id_belonging_to_another_machine_is_rejected_and_nothing_changes(): void
    {
        $this->actingAsManager();

        $machine = Machine::factory()->create();
        $foreign = MachineParameter::factory()->for(Machine::factory())->key('theirs', 'Theirs')->create();

        $this->put(route('machines.update', $machine), $this->machinePayload([
            'name' => 'Should not save',
            'machine_type_id' => $machine->machine_type_id,
            'machine_code' => $machine->machine_code,
            'parameters' => [['id' => $foreign->id, 'label' => 'Hijacked', 'data_type' => 'number']],
        ]))->assertSessionHasErrors('parameters');

        $this->assertSame('Theirs', $foreign->fresh()->label);
        $this->assertNotSame('Should not save', $machine->fresh()->name);
    }

    public function test_a_duplicate_key_on_new_rows_is_rejected_and_the_machine_is_not_created(): void
    {
        $this->actingAsManager();

        $this->post(route('machines.store'), $this->machinePayload(['parameters' => [
            ['label' => 'One', 'key' => 'same_key', 'data_type' => 'number'],
            ['label' => 'Two', 'key' => 'same_key', 'data_type' => 'number'],
        ]]))->assertSessionHasErrors('parameters');

        $this->assertDatabaseCount('machines', 0);
        $this->assertDatabaseCount('machine_parameters', 0);
    }

    public function test_invalid_parameter_rows_are_rejected(): void
    {
        $this->actingAsManager();

        $this->post(route('machines.store'), $this->machinePayload(['parameters' => [
            ['label' => '', 'data_type' => 'number'],
            ['label' => 'Bad key', 'key' => 'Bad Key!', 'data_type' => 'number'],
            ['label' => 'Bad type', 'data_type' => 'color'],
            ['label' => 'Inverted range', 'data_type' => 'number', 'min' => '10', 'max' => '5'],
            ['label' => 'Not numeric', 'data_type' => 'number', 'warning_max' => 'high'],
        ]]))->assertSessionHasErrors([
            'parameters.0.label',
            'parameters.1.key',
            'parameters.2.data_type',
            'parameters.3.min',
            'parameters.4.warning_max',
        ]);

        $this->assertDatabaseCount('machines', 0);
    }

    public function test_the_parameters_json_endpoint_returns_copyable_rows_for_a_viewer(): void
    {
        $this->actingAsManager();

        $source = Machine::factory()->create();
        MachineParameter::factory()->for($source)->key('coolant_temp', 'Coolant temperature', '°C')
            ->range(-20, 150)->withThresholds(['critical_max' => 100])->required()->create();

        $this->getJson(route('machines.parameters', $source))
            ->assertOk()
            ->assertExactJson([[
                'key' => 'coolant_temp',
                'label' => 'Coolant temperature',
                'unit' => '°C',
                'data_type' => 'number',
                'required' => true,
                'min' => -20,
                'max' => 150,
                'warning_min' => null,
                'warning_max' => null,
                'critical_min' => null,
                'critical_max' => 100,
            ]]);
    }

    public function test_the_parameters_json_endpoint_requires_the_machines_view_permission(): void
    {
        $this->actingAs(User::factory()->create());

        $this->getJson(route('machines.parameters', Machine::factory()->create()))->assertForbidden();
    }

    public function test_the_create_form_offers_machines_with_parameters_to_copy_from(): void
    {
        $this->actingAsManager();

        $withParameters = Machine::factory()->create(['name' => 'Has Parameters']);
        MachineParameter::factory()->for($withParameters)->create();
        Machine::factory()->create(['name' => 'Has None']);

        $this->get(route('machines.create'))
            ->assertOk()
            ->assertSee('Has Parameters')
            ->assertDontSee('Has None');
    }

    public function test_the_edit_form_is_seeded_with_the_machines_saved_parameters(): void
    {
        $this->actingAsManager();

        $machine = Machine::factory()->create();
        MachineParameter::factory()->for($machine)->key('coolant_temp', 'Coolant temperature', '°C')
            ->withThresholds(['critical_max' => 100])->create();

        $this->get(route('machines.edit', $machine))
            ->assertOk()
            ->assertSee('id="parameters"', false)
            ->assertSee('Coolant temperature')
            ->assertSee('coolant_temp');
    }

    public function test_the_show_page_documents_the_api_for_this_machines_own_parameters(): void
    {
        $this->actingAsManager();

        $machine = Machine::factory()->create();
        MachineParameter::factory()->for($machine)->key('coolant_temp', 'Coolant temperature', '°C')->range(0, 100)->create();
        MachineParameter::factory()->for($machine)->key('door_open')->boolean()->required()->create();
        $plainKey = $machine->generateDeviceKey();

        $response = $this->get(route('machines.show', $machine))->assertOk();

        $response->assertSee(route('api.machine-logs.store'), false)
            ->assertSee('X-Device-Key: YOUR_DEVICE_KEY', false)
            ->assertSee('coolant_temp', false)
            ->assertSee('door_open', false)
            ->assertSee('Unprocessable', false);

        // The example never embeds the real key, even for a user who is allowed to see it.
        $response->assertDontSee('X-Device-Key: '.$plainKey, false);
    }
}
