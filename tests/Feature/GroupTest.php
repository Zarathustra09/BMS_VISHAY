<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Machine;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GroupTest extends TestCase
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

    /** @param  array<int, string>  $permissions */
    private function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'role-'.uniqid()])->givePermissionTo($permissions));

        return $user;
    }

    public function test_an_admin_can_create_a_group_with_members_and_roles(): void
    {
        $member = User::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('groups.store'), [
                'name' => 'Line 1 Team',
                'description' => 'Looks after line 1',
                'user_ids' => [$member->id],
                'roles' => ['technician'],
                'permissions' => ['alerts.view'],
            ])
            ->assertRedirect();

        $group = Group::where('name', 'Line 1 Team')->firstOrFail();
        $this->assertTrue($group->users->contains($member));
        $this->assertTrue($group->hasRole('technician'));
        $this->assertTrue($group->hasDirectPermission('alerts.view'));
    }

    public function test_the_group_pages_need_the_groups_permissions(): void
    {
        $group = Group::factory()->create();
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        $this->actingAs($viewer)->get(route('groups.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('groups.create'))->assertForbidden();
        $this->actingAs($viewer)->post(route('groups.store'), ['name' => 'X'])->assertForbidden();
        $this->actingAs($viewer)->delete(route('groups.destroy', $group))->assertForbidden();

        $this->actingAs($this->admin())->get(route('groups.index'))->assertOk()->assertSee($group->name);
        $this->actingAs($this->admin())->get(route('groups.show', $group))->assertOk();
        $this->actingAs($this->admin())->get(route('groups.edit', $group))->assertOk();
        $this->actingAs($this->admin())->get(route('groups.create'))->assertOk();
    }

    public function test_a_member_inherits_the_permissions_of_the_groups_roles(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $group->assignRole('technician');

        $this->assertFalse(User::find($user->id)->can('machines.update'));

        $group->users()->attach($user);

        $this->assertTrue(User::find($user->id)->can('machines.update'));

        $group->users()->detach($user);

        $this->assertFalse(User::find($user->id)->can('machines.update'));
    }

    public function test_a_member_inherits_permissions_given_directly_to_the_group_and_the_middleware_honours_them(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $group->givePermissionTo('alerts.view');

        $this->actingAs($user)->get(route('alerts.index'))->assertForbidden();

        $group->users()->attach($user);

        $this->actingAs(User::find($user->id))->get(route('alerts.index'))->assertOk();
    }

    public function test_a_user_in_several_groups_gets_the_permissions_of_all_of_them(): void
    {
        $user = User::factory()->create();
        $first = Group::factory()->create()->givePermissionTo('alerts.view');
        $second = Group::factory()->create()->givePermissionTo('machines.view');
        $user->groups()->attach([$first->id, $second->id]);

        $user = User::find($user->id);

        $this->assertTrue($user->can('alerts.view'));
        $this->assertTrue($user->can('machines.view'));
        $this->assertFalse($user->can('machines.delete'));
    }

    public function test_a_switched_off_module_stays_denied_even_when_a_group_grants_it(): void
    {
        config(['modules.work-orders' => false]);

        $user = User::factory()->create();
        $group = Group::factory()->create()->givePermissionTo('work-orders.view');
        $group->users()->attach($user);

        $this->assertFalse(User::find($user->id)->can('work-orders.view'));
    }

    public function test_only_an_admin_can_give_a_group_the_admin_role(): void
    {
        $editor = $this->userWithPermissions(['groups.view', 'groups.create', 'groups.update']);

        $this->actingAs($editor)
            ->post(route('groups.store'), ['name' => 'Sneaky', 'roles' => ['admin']])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('groups', ['name' => 'Sneaky']);

        $adminGroup = Group::factory()->create();
        $adminGroup->assignRole('admin');

        $this->actingAs($editor)
            ->put(route('groups.update', $adminGroup), ['name' => 'Renamed', 'roles' => ['admin']])
            ->assertSessionHas('error');

        $this->assertNotSame('Renamed', $adminGroup->fresh()->name);
    }

    public function test_a_group_used_as_a_machines_assignment_group_cannot_be_deleted(): void
    {
        $group = Group::factory()->create();
        Machine::factory()->create(['assignment_group_id' => $group->id]);

        $this->actingAs($this->admin())
            ->delete(route('groups.destroy', $group))
            ->assertRedirect(route('groups.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('groups', ['id' => $group->id]);
    }

    public function test_an_unused_group_can_be_deleted(): void
    {
        $group = Group::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('groups.destroy', $group))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
    }

    public function test_a_role_held_by_a_group_cannot_be_deleted(): void
    {
        $role = Role::create(['name' => 'night-shift']);
        Group::factory()->create()->assignRole($role);

        $this->actingAs($this->admin())
            ->delete(route('roles.destroy', $role))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['name' => 'night-shift']);
    }

    public function test_the_group_name_must_be_unique(): void
    {
        Group::factory()->create(['name' => 'Maintenance']);

        $this->actingAs($this->admin())
            ->post(route('groups.store'), ['name' => 'Maintenance'])
            ->assertSessionHasErrors('name');
    }
}
