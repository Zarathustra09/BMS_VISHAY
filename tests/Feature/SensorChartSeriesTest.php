<?php

namespace Tests\Feature;

use App\Interfaces\MachineLogInterface;
use App\Models\Machine;
use App\Models\MachineLog;
use App\Models\MachineParameter;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SensorChartSeriesTest extends TestCase
{
    use RefreshDatabase;

    private Machine $machine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Frozen, so "2 minutes ago" and the auto range never straddle a clock tick.
        $this->travelTo(Carbon::parse('2026-10-08 12:00:00'));

        $user = User::factory()->create();
        $user->assignRole('manager');
        $this->actingAs($user);

        $this->machine = Machine::factory()->create();
        MachineParameter::factory()->for($this->machine)->key('coolant_temp', 'Coolant temperature', '°C')->create();
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private function series(array $query = []): \Illuminate\Testing\TestResponse
    {
        return $this->getJson(route('machines.readings', ['machine' => $this->machine] + $query));
    }

    private function storeReading(float $value, Carbon $at): void
    {
        app(MachineLogInterface::class)->store([
            'machine_id' => $this->machine->id,
            'log_type' => 'sensor_reading',
            'readings' => ['coolant_temp' => $value],
            'logged_at' => $at,
        ]);
    }

    /**
     * Many readings at once, bypassing store() (and its per-row alert check) — only the
     * stored rows matter to the series.
     */
    private function insertReadings(int $count, Carbon $first, int $everySeconds): void
    {
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'machine_id' => $this->machine->id,
                'log_type' => 'sensor_reading',
                'severity' => 'info',
                'readings' => json_encode(['coolant_temp' => 40 + ($i % 10)]),
                'logged_at' => $first->copy()->addSeconds($i * $everySeconds)->toDateTimeString(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            MachineLog::insert($chunk);
        }
    }

    public function test_a_one_minute_burst_is_shown_reading_by_reading_even_in_a_long_window(): void
    {
        $this->insertReadings(60, now()->subMinutes(3), 1);

        $response = $this->series(['range' => '24h'])->assertOk();

        $response->assertJsonPath('resolution', 'Raw readings')
            ->assertJsonPath('reading_count', 60)
            ->assertJsonCount(60, 'points');

        $this->assertSame([1], collect($response->json('points'))->pluck('n')->unique()->values()->all());
        $this->assertMatchesRegularExpression('/^\d{2}:\d{2}:\d{2}$/', $response->json('points.0.label'));
    }

    public function test_a_dense_series_is_averaged_into_buckets_sized_to_the_span_of_the_data(): void
    {
        // 400 readings 10s apart = a ~66 minute span: 30s buckets is the smallest that fits 150 points.
        $this->insertReadings(400, now()->subHours(2), 10);

        $response = $this->series(['range' => '24h'])->assertOk();

        $response->assertJsonPath('resolution', '30-second averages')
            ->assertJsonPath('reading_count', 400);

        $points = collect($response->json('points'));

        $this->assertLessThanOrEqual(150, $points->count());
        $this->assertSame(400, $points->sum('n'));
        $this->assertSame(45, $response->json('gap_seconds'));
    }

    public function test_auto_picks_the_smallest_preset_that_contains_the_latest_reading(): void
    {
        $this->storeReading(40, now()->subMinutes(2));

        $this->series()->assertOk()
            ->assertJsonPath('requested_range', 'auto')
            ->assertJsonPath('range', '5m')
            ->assertJsonCount(1, 'points');
    }

    public function test_auto_widens_for_an_older_latest_reading(): void
    {
        $this->storeReading(40, now()->subHours(2));

        $this->series()->assertOk()->assertJsonPath('range', '6h')->assertJsonCount(1, 'points');
    }

    public function test_auto_falls_back_to_24h_with_no_readings_and_30d_when_the_latest_is_older_than_every_preset(): void
    {
        $this->series()->assertOk()
            ->assertJsonPath('range', '24h')
            ->assertJsonPath('latest_reading_at', null);

        $this->storeReading(40, now()->subDays(45));

        $this->series()->assertOk()
            ->assertJsonPath('range', '30d')
            ->assertJsonCount(0, 'points')
            ->assertJsonPath('latest_reading_human', '1 month ago');
    }

    public function test_an_empty_window_still_reports_when_the_parameter_last_reported(): void
    {
        $this->storeReading(40, now()->subHours(3));

        $response = $this->series(['range' => '5m'])->assertOk();

        $response->assertJsonCount(0, 'points');
        $this->assertSame(now()->subHours(3)->toIso8601String(), Carbon::parse($response->json('latest_reading_at'))->toIso8601String());
        $this->assertNotNull($response->json('latest_reading_from_input'));
    }

    public function test_a_custom_range_returns_only_readings_inside_it_and_a_past_window_is_not_polled(): void
    {
        $this->storeReading(40, Carbon::parse('2026-10-08 10:00:00'));
        $this->storeReading(41, Carbon::parse('2026-10-08 10:30:00'));
        $this->storeReading(42, Carbon::parse('2026-10-08 11:30:00'));

        $response = $this->series(['range' => 'custom', 'from' => '2026-10-08 09:50', 'to' => '2026-10-08 11:00'])->assertOk();

        $response->assertJsonPath('range', 'custom')
            ->assertJsonPath('reading_count', 2)
            ->assertJsonPath('poll_seconds', null);

        $this->assertEquals([40, 41], collect($response->json('points'))->pluck('avg')->all());
    }

    public function test_a_custom_range_ending_now_keeps_polling(): void
    {
        $this->series(['range' => 'custom', 'from' => '2026-10-08 11:30', 'to' => '2026-10-08 12:30'])
            ->assertOk()
            ->assertJsonPath('poll_seconds', 10);
    }

    public function test_the_poll_interval_follows_the_window_length(): void
    {
        $this->series(['range' => '5m'])->assertJsonPath('poll_seconds', 5);
        $this->series(['range' => '1h'])->assertJsonPath('poll_seconds', 10);
        $this->series(['range' => '24h'])->assertJsonPath('poll_seconds', 30);
    }

    public function test_a_gap_in_the_data_is_wide_enough_to_break_the_line_only_when_it_is_unusual(): void
    {
        // One reading a second: normal spacing, so the minimum of 5 seconds applies.
        $this->insertReadings(20, now()->subMinutes(3), 1);
        $this->series(['range' => '1h'])->assertJsonPath('gap_seconds', 5);

        // A device reporting every 10 minutes tolerates a 30-minute silence before breaking.
        MachineLog::query()->delete();
        $this->insertReadings(5, now()->subHours(2), 600);
        $this->series(['range' => '6h'])->assertJsonPath('gap_seconds', 1800);
    }

    public function test_an_invalid_range_is_rejected(): void
    {
        $this->series(['range' => '2h'])->assertJsonValidationErrors('range');
        $this->series(['range' => 'custom'])->assertJsonValidationErrors(['from', 'to']);
        $this->series(['range' => 'custom', 'from' => '2026-10-08 11:00', 'to' => '2026-10-08 10:00'])
            ->assertJsonValidationErrors('to');
        $this->series(['range' => 'custom', 'from' => '2026-06-01 00:00', 'to' => '2026-10-01 00:00'])
            ->assertJsonValidationErrors('to');
    }
}
