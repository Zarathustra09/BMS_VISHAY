<?php

namespace Tests\Feature;

use App\Interfaces\MachineLogInterface;
use App\Models\Group;
use App\Models\Machine;
use App\Models\MachineParameter;
use App\Models\MachineType;
use App\Models\User;
use App\Notifications\MachineAlertNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MachineAssignmentGroupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function breachingReading(Machine $machine): void
    {
        MachineParameter::factory()->for($machine)->key('temperature')->withThresholds(['critical_max' => 40])->create();

        app(MachineLogInterface::class)->store([
            'machine_id' => $machine->id,
            'log_type' => 'sensor_reading',
            'readings' => ['temperature' => 45],
        ]);
    }

    public function test_an_alert_is_sent_to_every_member_of_the_assignment_group_and_to_admins(): void
    {
        Notification::fake();

        $admin = tap(User::factory()->create())->assignRole('admin');
        $first = User::factory()->create();
        $second = User::factory()->create();
        $outsider = User::factory()->create();

        $group = Group::factory()->create();
        $group->users()->attach([$first->id, $second->id]);
        $machine = Machine::factory()->create(['assignment_group_id' => $group->id]);

        $this->breachingReading($machine);

        Notification::assertSentTo([$admin, $first, $second], MachineAlertNotification::class);
        Notification::assertNotSentTo($outsider, MachineAlertNotification::class);
    }

    public function test_someone_who_is_both_a_manager_and_in_the_group_is_notified_once(): void
    {
        Notification::fake();

        $manager = tap(User::factory()->create())->assignRole('manager');
        $group = Group::factory()->create();
        $group->users()->attach($manager);
        $machine = Machine::factory()->create(['assignment_group_id' => $group->id]);

        $this->breachingReading($machine);

        Notification::assertSentToTimes($manager, MachineAlertNotification::class, 1);
    }

    public function test_a_machine_without_a_group_still_alerts_the_admins_only(): void
    {
        Notification::fake();

        $admin = tap(User::factory()->create())->assignRole('admin');
        $member = User::factory()->create();
        Group::factory()->create()->users()->attach($member);
        $machine = Machine::factory()->create();

        $this->breachingReading($machine);

        Notification::assertSentTo($admin, MachineAlertNotification::class);
        Notification::assertNotSentTo($member, MachineAlertNotification::class);
    }

    public function test_the_assignment_group_can_be_set_and_cleared_from_the_machine_form(): void
    {
        $admin = tap(User::factory()->create())->assignRole('admin');
        $group = Group::factory()->create();
        $machine = Machine::factory()->create();
        $payload = [
            'name' => $machine->name,
            'machine_code' => $machine->machine_code,
            'machine_type_id' => MachineType::factory()->create()->id,
            'status' => 'active',
        ];

        $this->actingAs($admin)
            ->put(route('machines.update', $machine), $payload + ['assignment_group_id' => $group->id])
            ->assertSessionHasNoErrors();
        $this->assertSame($group->id, $machine->fresh()->assignment_group_id);

        $this->actingAs($admin)
            ->put(route('machines.update', $machine), $payload + ['assignment_group_id' => ''])
            ->assertSessionHasNoErrors();
        $this->assertNull($machine->fresh()->assignment_group_id);

        $this->actingAs($admin)
            ->put(route('machines.update', $machine), $payload + ['assignment_group_id' => 999999])
            ->assertSessionHasErrors('assignment_group_id');
    }

    public function test_the_machine_page_shows_the_assignment_group(): void
    {
        $admin = tap(User::factory()->create())->assignRole('admin');
        $group = Group::factory()->create(['name' => 'Night Shift Crew']);
        $machine = Machine::factory()->create(['assignment_group_id' => $group->id]);

        $this->actingAs($admin)->get(route('machines.show', $machine))
            ->assertOk()
            ->assertSee('Night Shift Crew')
            ->assertSee(route('groups.show', $group), false);
    }
}
