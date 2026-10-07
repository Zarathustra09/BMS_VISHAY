<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionDeniedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_a_viewer_cannot_create_a_building(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        $this->actingAs($viewer)
            ->post(route('buildings.store'), ['name' => 'New Building'])
            ->assertForbidden();
    }

    public function test_a_viewer_cannot_delete_a_machine(): void
    {
        $machine = Machine::factory()->create();
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        $this->actingAs($viewer)
            ->delete(route('machines.destroy', $machine))
            ->assertForbidden();
    }

    public function test_a_technician_sees_the_services_divider_but_not_the_system_divider(): void
    {
        $technician = User::factory()->create();
        $technician->assignRole('technician');

        $response = $this->actingAs($technician)->get(route('home'));

        $response->assertOk();
        $response->assertSee('<div class="divider text-xs opacity-50 my-2">SERVICES</div>', false);
        $response->assertDontSee('<div class="divider text-xs opacity-50 my-2">SYSTEM</div>', false);
    }

    public function test_a_technician_cannot_open_the_settings_page(): void
    {
        $technician = User::factory()->create();
        $technician->assignRole('technician');

        $this->actingAs($technician)
            ->get(route('settings.index'))
            ->assertForbidden();
    }

    public function test_a_technician_cannot_access_the_roles_index(): void
    {
        $technician = User::factory()->create();
        $technician->assignRole('technician');

        $this->actingAs($technician)
            ->get(route('roles.index'))
            ->assertForbidden();
    }

    public function test_a_guest_is_redirected_to_login_instead_of_a_403(): void
    {
        $this->get(route('buildings.index'))->assertRedirect(route('login'));
    }

    public function test_an_admin_can_open_the_settings_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('settings.index'))
            ->assertOk();
    }
}
