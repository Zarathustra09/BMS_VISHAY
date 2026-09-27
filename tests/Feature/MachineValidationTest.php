<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Machine;
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
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Chiller Unit A',
            'machine_code' => 'MC-1001',
            'type' => 'Chiller',
            'status' => 'active',
            'capacity_tons' => 200,
        ], $overrides);
    }

    public function test_creating_a_machine_with_an_invalid_type_is_rejected(): void
    {
        $this->actingAsManager();

        $response = $this->post(route('machines.store'), $this->validPayload(['type' => 'Not A Real Type']));

        $response->assertSessionHasErrors('type');
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
    }

    public function test_unchecked_toggle_fields_persist_as_false(): void
    {
        $this->actingAsManager();

        $this->post(route('machines.store'), $this->validPayload());

        $machine = Machine::sole();
        $this->assertFalse($machine->is_critical);
        $this->assertFalse($machine->autostart);
        $this->assertFalse($machine->makeup_valve);
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
