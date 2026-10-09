<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.demo_device_key' => 'test-device-key',
            'modules.buildings' => true,
            'modules.work-orders' => true,
        ]);

        $this->seed();
    }

    public function test_it_seeds_exactly_an_admin_and_a_viewer(): void
    {
        $this->assertSame(2, User::count());

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $viewer = User::where('email', 'viewer@example.com')->firstOrFail();

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->can('settings.update'));
        $this->assertTrue($viewer->hasRole('viewer'));
        $this->assertEqualsCanonicalizing(
            ['machines.view', 'machines.update', 'machines.delete', 'machines.view-device-key', 'floors.view'],
            $viewer->getAllPermissions()->pluck('name')->all()
        );
    }

    public function test_it_seeds_one_machine_that_accepts_readings_with_the_demo_key(): void
    {
        $this->assertSame(1, Machine::count());
        $this->assertSame(
            ['temperature', 'humidity'],
            Machine::firstOrFail()->parameters->pluck('key')->all()
        );

        $this->postJson('/api/machine-logs', [
            'log_type' => 'sensor_reading',
            'readings' => ['temperature' => 25.4, 'humidity' => 61],
        ], ['X-Device-Key' => 'test-device-key'])->assertCreated();
    }

    public function test_the_viewer_can_manage_machines_but_not_create_or_reach_other_modules(): void
    {
        $viewer = User::where('email', 'viewer@example.com')->firstOrFail();

        $this->actingAs($viewer)->get(route('home'))
            ->assertOk()
            ->assertDontSee('Building Health')
            ->assertDontSee('Recent Work Orders');

        $this->actingAs($viewer)->get(route('machines.index'))->assertOk();
        $this->actingAs($viewer)->get(route('machines.edit', Machine::firstOrFail()))->assertOk();
        $this->actingAs($viewer)->get(route('machines.create'))->assertForbidden();
        $this->actingAs($viewer)->get(route('buildings.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('alerts.index'))->assertForbidden();
    }

    public function test_the_viewer_cannot_create_a_machine_and_never_sees_the_add_button(): void
    {
        $viewer = User::where('email', 'viewer@example.com')->firstOrFail();

        $this->actingAs($viewer)->get(route('machines.index'))
            ->assertOk()
            ->assertDontSee('Add Machine')
            ->assertDontSee(route('machines.create'), false);

        $this->actingAs($viewer)->get(route('machines.create'))->assertForbidden();

        $this->actingAs($viewer)->post(route('machines.store'), [
            'name' => 'Sneaky Machine',
            'machine_code' => 'SNEAK-001',
            'status' => 'active',
        ])->assertForbidden();

        $this->assertSame(1, Machine::count());
    }

    public function test_the_admin_does_see_the_add_machine_button(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)->get(route('machines.index'))
            ->assertOk()
            ->assertSee(route('machines.create'), false);
    }
}
