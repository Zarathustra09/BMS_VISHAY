<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Machine;
use App\Models\MachineType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineValidationTest extends TestCase
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
     * The seeded default type — see 2026_09_27_*_seed_default_machine_types.php — whose only
     * spec field is `capacity_tons`.
     */
    private function chillerTypeId(): int
    {
        return MachineType::where('name', 'Chiller')->value('id');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Chiller Unit A',
            'machine_code' => 'MC-1001',
            'machine_type_id' => $this->chillerTypeId(),
            'status' => 'active',
            'specifications' => ['capacity_tons' => 200],
        ], $overrides);
    }

    public function test_creating_a_machine_with_an_invalid_type_is_rejected(): void
    {
        $this->actingAsManager();

        $response = $this->post(route('machines.store'), $this->validPayload(['machine_type_id' => 999999]));

        $response->assertSessionHasErrors('machine_type_id');
        $this->assertDatabaseCount('machines', 0);
    }

    public function test_creating_a_machine_with_a_non_ip_address_is_rejected(): void
    {
        $this->actingAsManager();

        $response = $this->post(route('machines.store'), $this->validPayload(['ip_address' => 'not-an-ip']));

        $response->assertSessionHasErrors('ip_address');
        $this->assertDatabaseCount('machines', 0);
    }

    public function test_a_floor_belonging_to_a_different_building_is_rejected(): void
    {
        $this->actingAsManager();

        $buildingA = Building::factory()->create();
        $buildingB = Building::factory()->create();
        $floorInOtherBuilding = Floor::create([
            'building_id' => $buildingB->id,
            'name' => 'Floor 1',
            'floor_number' => 1,
            'status' => 'active',
        ]);

        $response = $this->post(route('machines.store'), $this->validPayload([
            'building_id' => $buildingA->id,
            'floor_id' => $floorInOtherBuilding->id,
        ]));

        $response->assertSessionHasErrors('floor_id');
        $this->assertDatabaseCount('machines', 0);
    }

    public function test_next_maintenance_date_before_last_maintenance_date_is_rejected(): void
    {
        $this->actingAsManager();

        $response = $this->post(route('machines.store'), $this->validPayload([
            'last_maintenance_date' => '2026-06-15',
            'next_maintenance_date' => '2026-06-01',
        ]));

        $response->assertSessionHasErrors('next_maintenance_date');
        $this->assertDatabaseCount('machines', 0);
    }

    public function test_a_full_valid_payload_creates_the_machine(): void
    {
        $this->actingAsManager();

        $building = Building::factory()->create();
        $floor = Floor::create([
            'building_id' => $building->id,
            'name' => 'Floor 1',
            'floor_number' => 1,
            'status' => 'active',
        ]);

        $response = $this->post(route('machines.store'), $this->validPayload([
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'ip_address' => '10.0.0.5',
            'last_maintenance_date' => '2026-01-01',
            'next_maintenance_date' => '2026-06-01',
        ]));

        $machine = Machine::sole();
        $response->assertRedirect(route('machines.show', $machine));
        $this->assertSame('Chiller Unit A', $machine->name);
        $this->assertSame($floor->id, $machine->floor_id);
        // The float cast happens before storage, but json_encode drops the ".0" for a
        // whole-number float, so it round-trips back out of the `array` cast as an int.
        $this->assertSame(['capacity_tons' => 200], $machine->specifications);
    }

    public function test_unchecked_toggle_fields_persist_as_false(): void
    {
        $this->actingAsManager();

        $this->post(route('machines.store'), $this->validPayload());

        $machine = Machine::sole();
        $this->assertFalse($machine->is_critical);
    }

    public function test_non_numeric_value_for_a_number_spec_is_rejected_with_its_label(): void
    {
        $this->actingAsManager();

        $response = $this->post(route('machines.store'), $this->validPayload([
            'specifications' => ['capacity_tons' => 'not-a-number'],
        ]));

        $response->assertInvalid(['specifications.capacity_tons' => 'Capacity']);
        $this->assertDatabaseCount('machines', 0);
    }

    public function test_required_spec_field_is_enforced(): void
    {
        $this->actingAsManager();

        $type = MachineType::factory()->withFields([
            ['key' => 'pressure', 'label' => 'Pressure', 'type' => 'number', 'unit' => 'bar', 'step' => '0.1', 'min' => 0, 'required' => true],
        ])->create();

        $response = $this->post(route('machines.store'), $this->validPayload([
            'machine_type_id' => $type->id,
            'specifications' => [],
        ]));

        $response->assertSessionHasErrors('specifications.pressure');
        $this->assertDatabaseCount('machines', 0);
    }

    public function test_spec_keys_from_another_type_are_not_stored(): void
    {
        $this->actingAsManager();

        $response = $this->post(route('machines.store'), $this->validPayload([
            // `cells` belongs to Cooling Tower, not Chiller — the service must strip it even
            // though nothing in validation forbids submitting an unrelated key.
            'specifications' => ['capacity_tons' => 200, 'cells' => 4],
        ]));

        $response->assertSessionHasNoErrors();
        $machine = Machine::sole();
        $this->assertSame(['capacity_tons' => 200], $machine->specifications);
    }

    public function test_a_select_value_outside_its_options_is_rejected(): void
    {
        $this->actingAsManager();

        $type = MachineType::factory()->withFields([
            ['key' => 'fuel', 'label' => 'Fuel', 'type' => 'select', 'options' => ['Diesel', 'Natural Gas'], 'required' => false],
        ])->create();

        $response = $this->post(route('machines.store'), $this->validPayload([
            'machine_type_id' => $type->id,
            'specifications' => ['fuel' => 'Nuclear'],
        ]));

        $response->assertSessionHasErrors('specifications.fuel');
        $this->assertDatabaseCount('machines', 0);
    }

    public function test_a_multiselect_value_is_stored_as_an_array(): void
    {
        $this->actingAsManager();

        $type = MachineType::factory()->withFields([
            ['key' => 'certifications', 'label' => 'Certifications', 'type' => 'multiselect', 'options' => ['ISO 9001', 'ISO 14001', 'OSHA'], 'required' => false],
        ])->create();

        $this->post(route('machines.store'), $this->validPayload([
            'machine_type_id' => $type->id,
            'specifications' => ['certifications' => ['ISO 9001', 'OSHA']],
        ]));

        $machine = Machine::sole();
        $this->assertSame(['ISO 9001', 'OSHA'], $machine->specifications['certifications']);
    }

    public function test_an_invalid_multiselect_option_is_rejected(): void
    {
        $this->actingAsManager();

        $type = MachineType::factory()->withFields([
            ['key' => 'certifications', 'label' => 'Certifications', 'type' => 'multiselect', 'options' => ['ISO 9001', 'ISO 14001'], 'required' => false],
        ])->create();

        $response = $this->post(route('machines.store'), $this->validPayload([
            'machine_type_id' => $type->id,
            'specifications' => ['certifications' => ['Not A Real Cert']],
        ]));

        $response->assertSessionHasErrors('specifications.certifications.0');
        $this->assertDatabaseCount('machines', 0);
    }

    public function test_an_unchecked_boolean_spec_is_stored_as_false_and_shown_as_no(): void
    {
        $this->actingAsManager();

        $type = MachineType::factory()->withFields([
            ['key' => 'autostart', 'label' => 'Autostart', 'type' => 'boolean', 'required' => false],
        ])->create();

        // The toggle's hidden-0 input is what actually submits "0" when unchecked — real
        // unchecked-checkbox behaviour on the live form, reproduced directly here.
        $this->post(route('machines.store'), $this->validPayload([
            'machine_type_id' => $type->id,
            'specifications' => ['autostart' => '0'],
        ]));

        $machine = Machine::sole();
        $this->assertFalse($machine->specifications['autostart']);

        $response = $this->get(route('machines.show', $machine));
        $response->assertOk();
        $response->assertSee('Autostart');
        $response->assertSee('No');
    }

    public function test_an_invalid_date_spec_is_rejected(): void
    {
        $this->actingAsManager();

        $type = MachineType::factory()->withFields([
            ['key' => 'last_calibrated', 'label' => 'Last Calibrated', 'type' => 'date', 'required' => false],
        ])->create();

        $response = $this->post(route('machines.store'), $this->validPayload([
            'machine_type_id' => $type->id,
            'specifications' => ['last_calibrated' => 'not-a-date'],
        ]));

        $response->assertSessionHasErrors('specifications.last_calibrated');
        $this->assertDatabaseCount('machines', 0);
    }

    public function test_a_javascript_url_spec_is_rejected(): void
    {
        $this->actingAsManager();

        $type = MachineType::factory()->withFields([
            ['key' => 'manual_url', 'label' => 'Manual URL', 'type' => 'url', 'required' => false],
        ])->create();

        $response = $this->post(route('machines.store'), $this->validPayload([
            'machine_type_id' => $type->id,
            'specifications' => ['manual_url' => 'javascript:alert(1)'],
        ]));

        $response->assertSessionHasErrors('specifications.manual_url');
        $this->assertDatabaseCount('machines', 0);
    }

    public function test_activity_feed_renders_after_a_multiselect_and_boolean_change(): void
    {
        $this->actingAsManager();

        $type = MachineType::factory()->withFields([
            ['key' => 'certifications', 'label' => 'Certifications', 'type' => 'multiselect', 'options' => ['ISO 9001', 'OSHA'], 'required' => false],
            ['key' => 'autostart', 'label' => 'Autostart', 'type' => 'boolean', 'required' => false],
        ])->create();

        $machine = Machine::factory()->create([
            'machine_type_id' => $type->id,
            'specifications' => ['certifications' => ['ISO 9001'], 'autostart' => false],
        ]);

        $this->put(route('machines.update', $machine), $this->validPayload([
            'machine_type_id' => $type->id,
            'specifications' => ['certifications' => ['ISO 9001', 'OSHA'], 'autostart' => '1'],
        ]));

        // Both a multiselect (array) and a boolean diff must be flattened to scalars by
        // MachineService::expandSpecificationChanges() before the view echoes them.
        $response = $this->get(route('machines.show', $machine));

        $response->assertOk();
        $response->assertSee('Certifications');
        $response->assertSee('Autostart');
    }

    public function test_create_page_lists_database_machine_types(): void
    {
        $this->actingAsManager();

        MachineType::factory()->create(['name' => 'Custom Widget Press']);

        $response = $this->get(route('machines.create'));

        $response->assertOk();
        $response->assertSee('Custom Widget Press');
    }

    public function test_show_page_renders_stored_specs_with_units(): void
    {
        $this->actingAsManager();

        $machine = Machine::factory()->create([
            'machine_type_id' => $this->chillerTypeId(),
            'specifications' => ['capacity_tons' => 250],
        ]);

        $response = $this->get(route('machines.show', $machine));

        $response->assertOk();
        $response->assertSee('Capacity');
        $response->assertSee('250');
        $response->assertSee('tons');
    }

    public function test_edit_page_prefills_stored_spec_values(): void
    {
        $this->actingAsManager();

        $machine = Machine::factory()->create([
            'machine_type_id' => $this->chillerTypeId(),
            'specifications' => ['capacity_tons' => 175],
        ]);

        $response = $this->get(route('machines.edit', $machine));

        $response->assertOk();
        // The value is seeded into Alpine's specValues object (@js()), not a static HTML
        // attribute — this confirms the machine's stored spec actually reached the page.
        $response->assertSee('175', false);
        $response->assertSee('Capacity', false);
    }

    public function test_activity_feed_renders_after_a_spec_change(): void
    {
        $this->actingAsManager();

        $machine = Machine::factory()->create([
            'machine_type_id' => $this->chillerTypeId(),
            'specifications' => ['capacity_tons' => 100],
        ]);

        $this->put(route('machines.update', $machine), $this->validPayload([
            'specifications' => ['capacity_tons' => 300],
        ]));

        // MachineService::expandSpecificationChanges() must flatten the `specifications` diff
        // into scalar old/new pairs before the view echoes them — otherwise this throws.
        $response = $this->get(route('machines.show', $machine));

        $response->assertOk();
        $response->assertSee('Capacity');
    }

    /**
     * The POST-only tests above never render machines/_form.blade.php with a null $machine —
     * that's exactly the gap that let a fatal "Attempt to read property on null" ship on the
     * create page ($machine->last_maintenance_date?->format(...) doesn't suppress the warning
     * the way a plain $machine->prop ?? '' does). This guards that branch directly.
     */
    public function test_the_machine_create_page_renders(): void
    {
        $this->actingAsManager();

        $response = $this->get(route('machines.create'));

        $response->assertOk();
        $response->assertSee('New Machine');

        // Regression guard: x-data must be seeded with @js(), not @json() — @json()'s double
        // quotes terminate the HTML attribute early, silently breaking every Alpine binding
        // on the page (the floor dropdown never populating, specs never switching with type).
        $response->assertSee('machineForm(JSON.parse(', false);

        // Regression guard: a section title like "Location & Power" must render escaped
        // exactly once. Passing an already-escaped "&amp;" into <x-form.section title="…">
        // gets escaped a second time by the component's {{ $title }}, producing "&amp;amp;".
        $response->assertDontSee('&amp;amp;', false);
    }

    public function test_the_machine_edit_page_renders(): void
    {
        $this->actingAsManager();
        $machine = Machine::factory()->create(['name' => 'Chiller Unit Z']);

        $response = $this->get(route('machines.edit', $machine));

        $response->assertOk();
        $response->assertSee('Chiller Unit Z');
    }
}
