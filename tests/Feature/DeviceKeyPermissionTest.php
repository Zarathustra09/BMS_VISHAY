<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceKeyPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_a_role_without_the_permission_cannot_generate_a_device_key(): void
    {
        $machine = Machine::factory()->create();
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        $response = $this->actingAs($viewer)
            ->post(route('machines.device-key.generate', $machine));

        $response->assertForbidden();
    }

    public function test_a_role_with_the_permission_can_generate_and_view_a_device_key(): void
    {
        $machine = Machine::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->post(route('machines.device-key.generate', $machine));

        $response->assertRedirect(route('machines.show', $machine));
        $response->assertSessionHas('device_key_plain');
        $this->assertNotNull($machine->fresh()->decryptDeviceKey());
    }

    public function test_the_machine_show_page_hides_the_device_key_card_without_permission(): void
    {
        $machine = Machine::factory()->create();
        $machine->generateDeviceKey();
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        $response = $this->actingAs($viewer)->get(route('machines.show', $machine));

        $response->assertOk();
        $response->assertDontSee('Regenerate Key');
    }
}
