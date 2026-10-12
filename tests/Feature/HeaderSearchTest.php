<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MachineType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HeaderSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_it_returns_matching_machines_with_display_fields_and_a_link(): void
    {
        $type = MachineType::factory()->create(['name' => 'Hydraulic Press']);
        $machine = Machine::factory()->create([
            'name' => 'Stamping Press One',
            'machine_code' => 'MC-1001',
            'machine_type_id' => $type->id,
            'status' => 'fault',
        ]);
        Machine::factory()->create(['name' => 'Unrelated Lathe', 'machine_code' => 'MC-2002']);

        $response = $this->actingAs($this->userWithRole('technician'))
            ->getJson(route('search', ['q' => 'stamping']));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Stamping Press One')
            ->assertJsonPath('data.0.code', 'MC-1001')
            ->assertJsonPath('data.0.type', 'Hydraulic Press')
            ->assertJsonPath('data.0.status_label', 'Fault')
            ->assertJsonPath('data.0.status_badge', 'badge-error')
            ->assertJsonPath('data.0.url', route('machines.show', $machine));
    }

    public function test_it_matches_on_code_serial_number_and_model_number(): void
    {
        Machine::factory()->create(['name' => 'Alpha', 'machine_code' => 'ZX-CODE-77']);
        Machine::factory()->create(['name' => 'Bravo', 'machine_code' => 'MC-0002', 'serial_number' => 'SN-ABC-123']);
        Machine::factory()->create(['name' => 'Charlie', 'machine_code' => 'MC-0003', 'model_number' => 'MDL-9000']);
        $technician = $this->userWithRole('technician');

        $this->actingAs($technician)->getJson(route('search', ['q' => 'ZX-CODE']))
            ->assertJsonPath('data.0.name', 'Alpha');
        $this->actingAs($technician)->getJson(route('search', ['q' => 'ABC-123']))
            ->assertJsonPath('data.0.name', 'Bravo');
        $this->actingAs($technician)->getJson(route('search', ['q' => 'MDL-9']))
            ->assertJsonPath('data.0.name', 'Charlie');
    }

    public function test_it_returns_an_empty_list_when_nothing_matches(): void
    {
        Machine::factory()->create(['name' => 'Alpha']);

        $this->actingAs($this->userWithRole('technician'))
            ->getJson(route('search', ['q' => 'nomatchhere']))
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }

    public function test_results_are_limited_to_eight(): void
    {
        Machine::factory()->count(12)->sequence(fn ($sequence) => ['name' => 'Conveyor '.$sequence->index])->create();

        $this->actingAs($this->userWithRole('technician'))
            ->getJson(route('search', ['q' => 'Conveyor']))
            ->assertOk()
            ->assertJsonCount(8, 'data');
    }

    public function test_a_user_without_machines_view_is_forbidden(): void
    {
        Machine::factory()->create(['name' => 'Alpha']);
        $noRole = User::factory()->create();

        $this->actingAs($noRole)
            ->getJson(route('search', ['q' => 'Alpha']))
            ->assertForbidden();
    }

    public function test_a_viewer_cannot_call_the_search_endpoint(): void
    {
        Machine::factory()->create(['name' => 'Alpha']);

        $this->actingAs($this->userWithRole('viewer'))
            ->getJson(route('search', ['q' => 'Alpha']))
            ->assertForbidden();
    }

    public function test_a_role_with_machines_search_but_not_machines_view_is_forbidden(): void
    {
        Machine::factory()->create(['name' => 'Alpha']);
        $user = User::factory()->create();
        $user->givePermissionTo('machines.search');

        $this->actingAs($user)
            ->getJson(route('search', ['q' => 'Alpha']))
            ->assertForbidden();
    }

    public function test_a_viewer_gets_no_trace_of_the_search_in_the_page(): void
    {
        $response = $this->actingAs($this->userWithRole('viewer'))->get(route('home'));

        $response->assertOk()
            ->assertDontSee('headerSearch', false)
            ->assertDontSee('Search machines', false)
            ->assertDontSee(route('search'), false);
    }

    public function test_the_viewer_role_is_not_granted_machines_search(): void
    {
        $this->assertFalse(Role::findByName('viewer')->hasPermissionTo('machines.search'));
        $this->assertTrue(Role::findByName('manager')->hasPermissionTo('machines.search'));
        $this->assertTrue(Role::findByName('technician')->hasPermissionTo('machines.search'));
    }

    public function test_a_query_shorter_than_two_characters_is_rejected(): void
    {
        $this->actingAs($this->userWithRole('technician'))
            ->getJson(route('search', ['q' => 'a']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('search', ['q' => 'alpha']))->assertRedirect(route('login'));
    }

    public function test_the_machines_list_prefills_its_search_from_the_query_string(): void
    {
        $this->actingAs($this->userWithRole('technician'))
            ->get(route('machines.index', ['search' => 'press']))
            ->assertOk()
            ->assertSee("search: {search: 'press'}", false);
    }

    public function test_the_header_search_box_is_rendered_for_a_role_with_machines_search(): void
    {
        $this->actingAs($this->userWithRole('technician'))
            ->get(route('home'))
            ->assertOk()
            ->assertSee('headerSearch(', false);
    }
}
