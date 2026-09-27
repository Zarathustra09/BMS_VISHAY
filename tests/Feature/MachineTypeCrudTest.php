<?php

namespace Tests\Feature;

use App\Interfaces\MachineTypeInterface;
use App\Models\Machine;
use App\Models\MachineType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MachineTypeCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        return $admin;
    }

    private function actingAsManager(): User
    {
        $manager = User::factory()->create();
        $manager->assignRole('manager');
        $this->actingAs($manager);

        return $manager;
    }

    private function actingAsViewer(): User
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');
        $this->actingAs($viewer);

        return $viewer;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('machine-types.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_viewer_is_forbidden_from_creating_a_type(): void
    {
        $this->actingAsViewer();

        $response = $this->post(route('machine-types.store'), ['name' => 'Test Type']);

        $response->assertForbidden();
    }

    public function test_manager_is_forbidden_from_deleting_a_type(): void
    {
        $this->actingAsManager();
        $machineType = app(MachineTypeInterface::class)->create(['name' => 'Test Type']);

        $response = $this->delete(route('machine-types.destroy', $machineType));

        $response->assertForbidden();
    }

    public function test_creating_a_type_without_a_name_is_rejected(): void
    {
        $this->actingAsManager();

        $response = $this->post(route('machine-types.store'), []);

        $response->assertInvalid(['name' => 'required']);
        $this->assertDatabaseCount('machine_types', 9); // just the nine seeded defaults
    }

    public function test_creating_a_type_with_a_duplicate_name_is_rejected(): void
    {
        $this->actingAsManager();
        app(MachineTypeInterface::class)->create(['name' => 'Chiller Duplicate']);

        $response = $this->post(route('machine-types.store'), ['name' => 'Chiller Duplicate']);

        $response->assertInvalid(['name' => 'already been taken']);
    }

    #[DataProvider('invalidFieldKinds')]
    public function test_a_spec_field_kind_outside_text_or_number_is_rejected(string $kind): void
    {
        $this->actingAsManager();

        $response = $this->post(route('machine-types.store'), [
            'name' => 'Test Type',
            'spec_fields' => [
                ['label' => 'Something', 'type' => $kind],
            ],
        ]);

        $response->assertInvalid(['spec_fields.0.type']);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidFieldKinds(): array
    {
        return [
            'boolean kind' => ['boolean'],
            'select kind' => ['select'],
            'empty kind' => [''],
        ];
    }

    public function test_two_fields_generating_the_same_key_are_rejected(): void
    {
        $this->actingAsManager();

        $response = $this->post(route('machine-types.store'), [
            'name' => 'Test Type',
            'icon' => 'cog',
            'spec_fields' => [
                ['label' => 'Capacity', 'type' => 'number'],
                ['label' => 'Capacity', 'type' => 'text'],
            ],
        ]);

        $response->assertInvalid(['spec_fields.1.label' => 'distinct label']);
    }

    public function test_manager_creates_a_type_with_generated_field_keys(): void
    {
        $this->actingAsManager();

        $response = $this->post(route('machine-types.store'), [
            'name' => 'Custom Type',
            'icon' => 'lightning-bolt',
            'spec_fields' => [
                ['label' => 'Capacity', 'type' => 'number', 'unit' => 'tons', 'step' => '0.01', 'min' => '0', 'required' => '1'],
                ['label' => 'Notes', 'type' => 'text'],
            ],
        ]);

        $response->assertRedirect(route('machine-types.index'));

        $machineType = MachineType::where('name', 'Custom Type')->firstOrFail();
        $fields = $machineType->specFields();

        $this->assertCount(2, $fields);
        $this->assertSame('capacity', $fields[0]['key']);
        $this->assertSame('number', $fields[0]['type']);
        $this->assertSame('tons', $fields[0]['unit']);
        $this->assertTrue($fields[0]['required']);
        $this->assertSame('notes', $fields[1]['key']);
        $this->assertSame('text', $fields[1]['type']);
    }

    public function test_renaming_a_field_label_keeps_its_key(): void
    {
        $this->actingAsManager();
        $machineType = app(MachineTypeInterface::class)->create([
            'name' => 'Renaming Type',
            'spec_fields' => [
                ['label' => 'Capacity', 'type' => 'number', 'required' => false],
            ],
        ]);
        $originalKey = $machineType->specFields()[0]['key'];

        $response = $this->put(route('machine-types.update', $machineType), [
            'name' => 'Renaming Type',
            'icon' => 'cog',
            'spec_fields' => [
                ['key' => $originalKey, 'label' => 'Max Capacity', 'type' => 'number', 'required' => false],
            ],
        ]);

        $response->assertRedirect(route('machine-types.index'));

        $fields = $machineType->fresh()->specFields();
        $this->assertSame($originalKey, $fields[0]['key']);
        $this->assertSame('Max Capacity', $fields[0]['label']);
    }

    public function test_posting_a_key_that_never_belonged_to_this_type_is_rejected(): void
    {
        $this->actingAsManager();
        $machineType = app(MachineTypeInterface::class)->create([
            'name' => 'Foreign Key Type',
            'spec_fields' => [
                ['label' => 'Capacity', 'type' => 'number', 'required' => false],
            ],
        ]);

        // "borrowed_key" never belonged to this type — posting it directly (rather than
        // leaving `key` blank for a genuinely new field) must not be accepted, since it would
        // silently graft this row onto whatever "borrowed_key" already means elsewhere.
        $response = $this->put(route('machine-types.update', $machineType), [
            'name' => 'Foreign Key Type',
            'icon' => 'cog',
            'spec_fields' => [
                ['key' => 'borrowed_key', 'label' => 'Suspicious Field', 'type' => 'text'],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertSame('Capacity', $machineType->fresh()->specFields()[0]['label']);
    }

    public function test_number_only_attributes_are_stripped_from_text_fields(): void
    {
        $this->actingAsManager();

        $response = $this->post(route('machine-types.store'), [
            'name' => 'Text Only Type',
            'icon' => 'fire',
            'spec_fields' => [
                ['label' => 'Fuel', 'type' => 'text', 'unit' => 'ignored', 'step' => '1', 'min' => '0'],
            ],
        ]);

        $response->assertRedirect(route('machine-types.index'));

        $machineType = MachineType::where('name', 'Text Only Type')->firstOrFail();
        $field = $machineType->specFields()[0];

        $this->assertArrayNotHasKey('unit', $field);
        $this->assertArrayNotHasKey('step', $field);
        $this->assertArrayNotHasKey('min', $field);
    }

    public function test_an_icon_outside_the_predetermined_set_is_rejected(): void
    {
        $this->actingAsManager();

        $response = $this->post(route('machine-types.store'), [
            'name' => 'Bad Icon Type',
            'icon' => 'skull-and-crossbones',
        ]);

        $response->assertInvalid(['icon']);
        $this->assertDatabaseMissing('machine_types', ['name' => 'Bad Icon Type']);
    }

    public function test_seeded_types_use_icon_keys_not_emoji(): void
    {
        $chiller = MachineType::where('name', 'Chiller')->firstOrFail();

        $this->assertSame('cube', $chiller->icon);
    }

    public function test_index_renders_each_types_icon_svg(): void
    {
        $this->actingAsManager();

        $response = $this->get(route('machine-types.index'));

        $response->assertOk();
        // One <svg> per row rendered by <x-machine-type-icon> — proves the icon is drawn as
        // an SVG, not printed as raw emoji text.
        $response->assertSee('<svg', false);
    }

    public function test_admin_deletes_an_unused_type(): void
    {
        $this->actingAsAdmin();
        $machineType = app(MachineTypeInterface::class)->create(['name' => 'Unused Type']);

        $response = $this->delete(route('machine-types.destroy', $machineType));

        $response->assertRedirect(route('machine-types.index'));
        $this->assertDatabaseMissing('machine_types', ['id' => $machineType->id]);
    }

    public function test_type_used_by_a_machine_cannot_be_deleted(): void
    {
        $this->actingAsAdmin();
        $machineType = app(MachineTypeInterface::class)->create(['name' => 'In-Use Type']);
        Machine::factory()->create(['machine_type_id' => $machineType->id]);

        $response = $this->delete(route('machine-types.destroy', $machineType));

        $response->assertRedirect();
        $this->assertDatabaseHas('machine_types', ['id' => $machineType->id]);
    }

    public function test_type_used_only_by_a_soft_deleted_machine_cannot_be_deleted(): void
    {
        $this->actingAsAdmin();
        $machineType = app(MachineTypeInterface::class)->create(['name' => 'Soft-Deleted Machine Type']);
        $machine = Machine::factory()->create(['machine_type_id' => $machineType->id]);
        $machine->delete();

        $response = $this->delete(route('machine-types.destroy', $machineType));

        $response->assertRedirect();
        $this->assertDatabaseHas('machine_types', ['id' => $machineType->id]);
    }

    public function test_index_create_and_edit_pages_render(): void
    {
        $this->actingAsManager();
        $machineType = app(MachineTypeInterface::class)->create(['name' => 'Renderable Type']);

        $this->get(route('machine-types.index'))->assertOk()->assertSee('Renderable Type');
        $this->get(route('machine-types.create'))->assertOk();
        $this->get(route('machine-types.edit', $machineType))->assertOk()->assertSee('Renderable Type');
    }
}
