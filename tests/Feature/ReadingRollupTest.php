<?php

namespace Tests\Feature;

use App\Interfaces\ReadingRollupInterface;
use App\Models\Machine;
use App\Models\MachineLog;
use App\Models\MachineParameter;
use App\Models\MachineReadingRollup;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Per-minute summaries of sensor readings: how they are built, kept and pruned, and that the long
 * chart windows read them.
 */
class ReadingRollupTest extends TestCase
{
    use RefreshDatabase;

    private Machine $machine;

    private ReadingRollupInterface $rollups;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->travelTo(Carbon::parse('2026-10-12 12:10:00'));

        $this->machine = Machine::factory()->create();
        $this->machine->generateDeviceKey('rollup-key');
        MachineParameter::factory()->for($this->machine)->key('temperature', 'Temperature', '°C')->create(['sort_order' => 0]);
        MachineParameter::factory()->for($this->machine)->key('mode')->text()->create(['sort_order' => 1]);

        $this->rollups = app(ReadingRollupInterface::class);
    }

    /**
     * @param  array<string, mixed>  $readings
     * @param  array<string, mixed>|null  $stats
     */
    private function log(string $at, array $readings, ?array $stats = null, string $type = 'sensor_reading'): int
    {
        return DB::table('machine_logs')->insertGetId([
            'machine_id' => $this->machine->id,
            'log_type' => $type,
            'severity' => 'info',
            'readings' => $readings === [] ? null : json_encode($readings),
            'reading_stats' => $stats === null ? null : json_encode($stats),
            'logged_at' => $at,
        ]);
    }

    private function minute(string $at): ?object
    {
        return DB::table('machine_reading_rollups')
            ->where('machine_id', $this->machine->id)
            ->where('metric', 'temperature')
            ->where('period_start', Carbon::parse($at)->getTimestamp())
            ->first();
    }

    public function test_readings_are_summarised_per_minute(): void
    {
        $this->log('2026-10-12 12:00:10', ['temperature' => 10]);
        $this->log('2026-10-12 12:00:20', ['temperature' => 20]);
        $this->log('2026-10-12 12:01:05', ['temperature' => 30]);

        $result = $this->rollups->rollUp();

        $this->assertSame(3, $result['rows']);
        $this->assertSame(2, DB::table('machine_reading_rollups')->count());

        $first = $this->minute('2026-10-12 12:00:00');
        $this->assertEquals([10, 20, 30, 2], [$first->min, $first->max, $first->sum, $first->count]);

        $second = $this->minute('2026-10-12 12:01:00');
        $this->assertEquals([30, 30, 30, 1], [$second->min, $second->max, $second->sum, $second->count]);
    }

    public function test_a_merged_per_second_log_counts_all_of_its_samples_and_keeps_its_range(): void
    {
        $this->log('2026-10-12 12:00:10', ['temperature' => 50], ['temperature' => ['min' => 40, 'max' => 60, 'n' => 4]]);
        $this->log('2026-10-12 12:00:30', ['temperature' => 10]);

        $this->rollups->rollUp();

        $row = $this->minute('2026-10-12 12:00:00');
        $this->assertEquals([10, 60, 210, 5], [$row->min, $row->max, $row->sum, $row->count]);
    }

    public function test_only_number_parameters_are_summarised(): void
    {
        $this->log('2026-10-12 12:00:10', ['temperature' => 10, 'mode' => '99']);

        $this->rollups->rollUp();

        $this->assertSame(['temperature'], DB::table('machine_reading_rollups')->pluck('metric')->all());
    }

    public function test_running_it_again_changes_nothing(): void
    {
        $this->log('2026-10-12 12:00:10', ['temperature' => 10]);
        $this->log('2026-10-12 12:00:20', ['temperature' => 20]);

        $this->rollups->rollUp();
        $before = DB::table('machine_reading_rollups')->get()->toArray();

        $this->rollups->rollUp();

        $this->assertEquals($before, DB::table('machine_reading_rollups')->get()->toArray());
    }

    public function test_a_late_replayed_reading_corrects_its_old_minute(): void
    {
        $this->log('2026-10-12 08:00:10', ['temperature' => 10]);
        $this->rollups->rollUp();

        $this->log('2026-10-12 08:00:40', ['temperature' => 30]);
        $this->rollups->rollUp();

        $row = $this->minute('2026-10-12 08:00:00');
        $this->assertEquals([10, 30, 40, 2], [$row->min, $row->max, $row->sum, $row->count]);
    }

    public function test_the_rollup_follows_the_latest_second_that_keeps_growing(): void
    {
        // The current second's log is merged into after it was first summarised.
        $id = $this->log('2026-10-12 12:09:30', ['temperature' => 50], ['temperature' => ['min' => 50, 'max' => 50, 'n' => 1]]);
        $this->rollups->rollUp();

        DB::table('machine_logs')->where('id', $id)->update([
            'readings' => json_encode(['temperature' => 60]),
            'reading_stats' => json_encode(['temperature' => ['min' => 50, 'max' => 70, 'n' => 3]]),
        ]);
        $this->rollups->rollUp();

        $row = $this->minute('2026-10-12 12:09:00');
        $this->assertEquals([50, 70, 180, 3], [$row->min, $row->max, $row->sum, $row->count]);
    }

    public function test_a_summary_is_not_shrunk_when_some_of_its_logs_were_pruned(): void
    {
        $id = $this->log('2026-10-12 08:00:10', ['temperature' => 10]);
        $this->log('2026-10-12 08:00:20', ['temperature' => 20]);
        $this->rollups->rollUp();

        // One log pruned, and the cursor lost (cache cleared): everything is looked at again.
        DB::table('machine_logs')->where('id', $id)->delete();
        Cache::forget('readings-rollup-cursor');
        $this->rollups->rollUp();

        $this->assertSame(2, (int) $this->minute('2026-10-12 08:00:00')->count);
    }

    public function test_pruning_keeps_what_has_not_been_summarised_and_every_event(): void
    {
        $summarisedOld = $this->log('2026-10-01 10:00:00', ['temperature' => 10]);
        $recent = $this->log('2026-10-11 10:00:00', ['temperature' => 20]);
        $this->rollups->rollUp();

        $notSummarisedOld = $this->log('2026-10-01 10:00:05', ['temperature' => 30]);
        $oldAlarm = $this->log('2026-10-01 10:00:10', [], null, 'alarm');
        $ancient = $this->log('2025-01-01 10:00:00', ['temperature' => 40]);

        $this->artisan('model:prune', ['--model' => [MachineLog::class]])->assertSuccessful();

        $this->assertDatabaseMissing('machine_logs', ['id' => $summarisedOld]);
        $this->assertDatabaseHas('machine_logs', ['id' => $recent]);
        $this->assertDatabaseHas('machine_logs', ['id' => $notSummarisedOld]);
        $this->assertDatabaseHas('machine_logs', ['id' => $oldAlarm]);
        $this->assertDatabaseMissing('machine_logs', ['id' => $ancient]);

        // The summary of the pruned log outlives it.
        $this->assertNotNull($this->minute('2026-10-01 10:00:00'));
    }

    public function test_the_prune_window_follows_the_setting(): void
    {
        $this->log('2026-10-09 10:00:00', ['temperature' => 10]);
        $this->rollups->rollUp();

        $this->artisan('model:prune', ['--model' => [MachineLog::class]])->assertSuccessful();
        $this->assertSame(1, MachineLog::count());

        app(\App\Interfaces\SettingInterface::class)->update(['reading_retention_days' => 2]);

        $this->artisan('model:prune', ['--model' => [MachineLog::class]])->assertSuccessful();
        $this->assertSame(0, MachineLog::count());
    }

    public function test_old_summaries_are_pruned_with_the_machine_log_retention(): void
    {
        foreach (['2025-01-01 10:00:00' => 'ancient', '2026-09-01 10:00:00' => 'kept'] as $at => $label) {
            DB::table('machine_reading_rollups')->insert([
                'machine_id' => $this->machine->id, 'metric' => 'temperature',
                'period_start' => Carbon::parse($at)->getTimestamp(), 'min' => 1, 'max' => 2, 'sum' => 3, 'count' => 2,
            ]);
        }

        $this->artisan('model:prune', ['--model' => [MachineReadingRollup::class]])->assertSuccessful();

        $this->assertSame(1, MachineReadingRollup::count());
        $this->assertSame(Carbon::parse('2026-09-01 10:00:00')->getTimestamp(), MachineReadingRollup::first()->period_start);
    }

    public function test_the_command_reports_what_it_did(): void
    {
        $this->log('2026-10-12 12:00:10', ['temperature' => 10]);

        $this->artisan('app:rollup-readings')
            ->expectsOutputToContain('Read 1 new log(s)')
            ->assertSuccessful();
    }

    public function test_a_long_window_is_drawn_from_summaries_plus_the_newest_detailed_readings(): void
    {
        $user = User::factory()->create();
        $user->assignRole('manager');

        // Two hours of summaries, ten samples a minute; one minute (09:30) saw a spike to 99.
        $rows = [];

        for ($minute = 0; $minute < 120; $minute++) {
            $start = Carbon::parse('2026-10-12 08:00:00')->addMinutes($minute);

            $rows[] = [
                'machine_id' => $this->machine->id, 'metric' => 'temperature', 'period_start' => $start->getTimestamp(),
                'min' => 40, 'max' => $start->format('H:i') === '09:30' ? 99 : 60, 'sum' => 500, 'count' => 10,
            ];
        }

        DB::table('machine_reading_rollups')->insert($rows);

        // Newer than the last summary, so only the detailed log has it.
        $this->log('2026-10-12 11:59:30', ['temperature' => 70]);

        $response = $this->actingAs($user)
            ->getJson(route('machines.readings', ['machine' => $this->machine, 'range' => '24h']))
            ->assertOk()
            ->assertJsonPath('resolution', '5-minute averages')
            ->assertJsonPath('reading_count', 1201);

        $points = collect($response->json('points'));

        $this->assertCount(25, $points);
        $this->assertEquals(99, $points->firstWhere('t', Carbon::parse('2026-10-12 09:30:00')->getTimestamp())['max']);
        $this->assertEquals(50, $points->first()['avg']);
        $this->assertEquals(70, $points->last()['avg']);
    }

    public function test_a_second_of_merged_readings_keeps_its_range_on_a_short_chart(): void
    {
        $user = User::factory()->create();
        $user->assignRole('manager');

        $this->travelTo(Carbon::parse('2026-10-12 12:00:00'));

        foreach ([70, 90, 50] as $value) {
            $this->postJson('/api/machine-logs', ['log_type' => 'sensor_reading', 'readings' => ['temperature' => $value]], ['X-Device-Key' => 'rollup-key'])->assertCreated();
        }

        $point = $this->actingAs($user)
            ->getJson(route('machines.readings', ['machine' => $this->machine, 'range' => '5m']))
            ->assertOk()
            ->assertJsonPath('resolution', 'Raw readings')
            ->assertJsonPath('reading_count', 3)
            ->json('points.0');

        $this->assertEquals([70, 50, 90, 3], [$point['avg'], $point['min'], $point['max'], $point['n']]);
    }
}
