<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Machine;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MachineTabsAndBadgesTest extends TestCase
{
    use RefreshDatabase;

    private Machine $machine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->machine = Machine::factory()->create();
    }

    private function admin(): User
    {
        return tap(User::factory()->create())->assignRole('admin');
    }

    private function alert(string $status, string $severity = 'critical', ?Machine $machine = null): Alert
    {
        return Alert::create([
            'machine_id' => ($machine ?? $this->machine)->id, 'type' => 'threshold', 'metric' => 'temperature',
            'value' => 45, 'severity' => $severity, 'status' => $status, 'message' => "Too hot ({$status}).",
        ]);
    }

    private function unread(User $user, int $count): void
    {
        foreach (range(1, $count) as $i) {
            DatabaseNotification::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => 'x', 'notifiable_type' => User::class, 'notifiable_id' => $user->id,
                'data' => ['message' => "n{$i}"],
            ]);
        }
    }

    public function test_the_machine_page_is_split_into_tabs(): void
    {
        $this->actingAs($this->admin())->get(route('machines.show', $this->machine))
            ->assertOk()
            ->assertSee('role="tablist"', false)
            ->assertSee('id="panel-overview"', false)
            ->assertSee('id="panel-alerts"', false)
            ->assertSee('id="panel-logs"', false)
            ->assertSee('id="panel-details"', false)
            ->assertSee('id="panel-integration"', false)
            ->assertSee('id="panel-activity"', false);
    }

    public function test_the_alerts_tab_is_hidden_from_users_without_alerts_view(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'machines-only'])->givePermissionTo('machines.view'));
        $this->alert('open');

        $this->actingAs($user)->get(route('machines.show', $this->machine))
            ->assertOk()
            ->assertSee('id="panel-overview"', false)
            ->assertDontSee('id="panel-alerts"', false)
            ->assertDontSee('data-open-alert-count', false)
            ->assertDontSee('Needs attention');
    }

    public function test_the_tab_badge_and_kpi_show_the_open_alert_count(): void
    {
        $this->alert('open');
        $this->alert('open', 'warning');
        $this->alert('acknowledged');

        $html = $this->actingAs($this->admin())->get(route('machines.show', $this->machine))->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'data-open-alert-count'));
        $this->assertMatchesRegularExpression('/data-open-alert-count[^>]*>\s*2\s*</', $html);
    }

    public function test_an_empty_alerts_tab_explains_itself(): void
    {
        $this->actingAs($this->admin())->get(route('machines.show', $this->machine))
            ->assertOk()
            ->assertSee('No active alerts')
            ->assertSee('All clear');
    }

    public function test_the_panel_endpoint_returns_every_fragment_and_the_open_count(): void
    {
        $this->alert('open');

        $this->actingAs($this->admin())->getJson(route('machines.alerts-panel', $this->machine))
            ->assertOk()
            ->assertJsonPath('open_count', 1)
            ->assertJsonStructure(['open_count', 'active_html', 'trail_html', 'compact_html'])
            ->assertJsonFragment(['open_count' => 1]);
    }

    public function test_the_sidebar_badge_counts_open_alerts_not_unread_notifications(): void
    {
        $admin = $this->admin();
        $other = Machine::factory()->create();
        $this->alert('open');
        $this->alert('open', 'warning', $other);
        $this->alert('acknowledged');
        $this->alert('resolved');
        $this->unread($admin, 7);

        $html = $this->actingAs($admin)->get(route('home'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/id="sidebarAlertCount"[^>]*>\s*2\s*</', $html);
        $this->assertMatchesRegularExpression('/id="bellBadge"[^>]*>\s*7\s*</', $html);
    }

    public function test_the_bell_shows_a_number_and_caps_it(): void
    {
        $admin = $this->admin();
        $this->unread($admin, 120);

        $this->actingAs($admin)->get(route('home'))
            ->assertOk()
            ->assertSee('99+');
    }

    public function test_no_badge_is_rendered_visible_when_there_is_nothing_open(): void
    {
        $html = $this->actingAs($this->admin())->get(route('home'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/id="sidebarAlertCount"[^>]*class="[^"]*\bhidden\b/', $html.'');
        $this->assertStringContainsString('hidden', $html);
    }

    public function test_users_without_alerts_view_get_no_alert_badges(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'machines-only'])->givePermissionTo('machines.view'));
        $this->alert('open');

        $this->actingAs($user)->get(route('machines.index'))
            ->assertOk()
            ->assertDontSee('id="sidebarAlertCount"', false)
            ->assertDontSee('id="bellBadge"', false)
            ->assertDontSee('id="liveStatus"', false);
    }

    public function test_the_summary_endpoint_returns_fresh_counters(): void
    {
        $this->machine->forceFill(['last_seen_at' => now()->subMinutes(3)])->save();
        $this->alert('open');
        $this->machine->logs()->create(['log_type' => 'sensor_reading', 'severity' => 'critical', 'logged_at' => now()]);

        $this->actingAs($this->admin())->getJson(route('machines.summary', $this->machine))
            ->assertOk()
            ->assertJsonPath('open_count', 1)
            ->assertJsonPath('last_seen', '3 minutes ago')
            ->assertJsonPath('logs.total', 1)
            ->assertJsonPath('logs.today', 1)
            ->assertJsonPath('logs.critical', 1);
    }

    public function test_the_summary_hides_the_alert_count_from_users_without_alerts_view(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'machines-only'])->givePermissionTo('machines.view'));
        $this->alert('open');

        $this->actingAs($user)->getJson(route('machines.summary', $this->machine))
            ->assertOk()
            ->assertJsonPath('open_count', null);

        $this->actingAs(User::factory()->create())->getJson(route('machines.summary', $this->machine))->assertForbidden();
    }

    public function test_the_headline_numbers_are_marked_up_for_live_updates(): void
    {
        $html = $this->actingAs($this->admin())->get(route('machines.show', $this->machine))->assertOk()->getContent();

        foreach (['last_seen', 'last_seen_at', 'logs.today', 'logs.week', 'logs.total', 'logs.critical', 'logs.errors', 'logs.warnings'] as $key) {
            $this->assertStringContainsString('data-kpi="'.$key.'"', $html, "missing live hook {$key}");
        }
    }
}
