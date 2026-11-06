<?php

namespace Tests\Feature;

use App\Events\MachineReadingRecorded;
use App\Models\Alert;
use App\Models\Machine;
use App\Models\MachineParameter;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * A device posting every 100 ms must not turn into 10 rows, 10 broadcasts and 10 offline checks a
 * second: readings within one second are folded into one log.
 */
class HighFrequencyIngestionTest extends TestCase
{
    use RefreshDatabase;

    private Machine $machine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Frozen at a whole second, so "the same second" is exact.
        $this->travelTo(Carbon::parse('2026-10-12 12:00:00'));

        $this->machine = Machine::factory()->create();
        $this->machine->generateDeviceKey('fast-key');

        MachineParameter::factory()->for($this->machine)->key('temperature')
            ->withThresholds(['warning_max' => 80, 'critical_max' => 100])->create(['sort_order' => 0]);
        MachineParameter::factory()->for($this->machine)->key('door_open')->boolean()->create(['sort_order' => 1]);
        MachineParameter::factory()->for($this->machine)->key('mode')->text()->create(['sort_order' => 2]);
    }

    /** @param  array<string, mixed>  $extra */
    private function sendReading(array $readings, array $extra = []): TestResponse
    {
        return $this->postJson('/api/machine-logs', ['log_type' => 'sensor_reading', 'readings' => $readings] + $extra, ['X-Device-Key' => 'fast-key']);
    }

    public function test_readings_within_one_second_become_one_log_with_their_average_and_range(): void
    {
        foreach ([70, 72, 68, 74] as $value) {
            $this->sendReading(['temperature' => $value])->assertCreated();
        }

        $log = $this->machine->logs()->sole();

        $this->assertEquals(71, $log->readings['temperature']);
        $this->assertEquals(['min' => 68, 'max' => 74, 'n' => 4], $log->reading_stats['temperature']);
    }

    public function test_the_next_second_starts_a_new_log(): void
    {
        $this->sendReading(['temperature' => 70])->assertCreated();
        $this->sendReading(['temperature' => 72])->assertCreated();

        $this->travel(1)->second();

        $this->sendReading(['temperature' => 50])->assertCreated();

        $logs = $this->machine->logs()->orderBy('id')->get();

        $this->assertCount(2, $logs);
        $this->assertEquals(71, $logs[0]->readings['temperature']);
        $this->assertEquals(50, $logs[1]->readings['temperature']);
        $this->assertNull($logs[1]->reading_stats);
    }

    public function test_a_single_reading_is_stored_exactly_as_before(): void
    {
        $this->sendReading(['temperature' => 70, 'door_open' => true, 'mode' => 'eco'])->assertCreated();

        $log = $this->machine->logs()->sole();

        $this->assertEquals(['temperature' => 70, 'door_open' => true, 'mode' => 'eco'], $log->readings);
        $this->assertNull($log->reading_stats);
    }

    public function test_only_numbers_are_averaged_and_other_types_keep_the_latest_value(): void
    {
        $this->sendReading(['temperature' => 60, 'door_open' => false, 'mode' => '42'])->assertCreated();
        $this->sendReading(['temperature' => 80, 'door_open' => true, 'mode' => '7'])->assertCreated();

        $log = $this->machine->logs()->sole();

        $this->assertEquals(70, $log->readings['temperature']);
        $this->assertTrue($log->readings['door_open']);
        $this->assertSame('7', $log->readings['mode']);
        $this->assertSame(['temperature'], array_keys($log->reading_stats));
    }

    public function test_an_event_or_a_replayed_reading_is_never_merged(): void
    {
        $this->sendReading(['temperature' => 70])->assertCreated();
        $this->sendReading(['temperature' => 71], ['event' => 'Door check'])->assertCreated();
        $this->sendReading(['temperature' => 72], ['notes' => 'by hand'])->assertCreated();
        $this->sendReading(['temperature' => 73], ['logged_at' => now()->toDateTimeString()])->assertCreated();

        $this->assertSame(4, $this->machine->logs()->count());
    }

    public function test_a_log_of_another_machine_is_not_merged_into(): void
    {
        $other = Machine::factory()->create();
        $other->generateDeviceKey('other-key');
        MachineParameter::factory()->for($other)->key('temperature')->create();

        $this->sendReading(['temperature' => 70])->assertCreated();
        $this->postJson('/api/machine-logs', ['log_type' => 'sensor_reading', 'readings' => ['temperature' => 20]], ['X-Device-Key' => 'other-key'])->assertCreated();

        $this->assertSame(1, $this->machine->logs()->count());
        $this->assertSame(1, $other->logs()->count());
    }

    public function test_a_second_of_readings_broadcasts_once_not_once_per_reading(): void
    {
        Event::fake([MachineReadingRecorded::class]);

        foreach ([70, 71, 72, 73, 74] as $value) {
            $this->sendReading(['temperature' => $value])->assertCreated();
        }

        Event::assertDispatchedTimes(MachineReadingRecorded::class, 1);

        $this->travel(1)->second();
        $this->sendReading(['temperature' => 75])->assertCreated();

        Event::assertDispatchedTimes(MachineReadingRecorded::class, 2);
    }

    public function test_the_device_is_marked_as_seen_by_the_seconds_first_reading(): void
    {
        $this->sendReading(['temperature' => 70])->assertCreated();

        $this->assertTrue($this->machine->fresh()->last_seen_at->equalTo(now()));
    }

    public function test_a_one_reading_spike_inside_a_second_raises_an_alert_with_the_spike_value(): void
    {
        $this->sendReading(['temperature' => 70])->assertCreated();
        $this->sendReading(['temperature' => 120])->assertCreated();
        $this->sendReading(['temperature' => 70])->assertCreated();

        $alert = Alert::sole();

        $this->assertSame('critical', $alert->severity);
        $this->assertEquals(120, $alert->value);

        // The log's average (87) alone would only be a warning: the spike still marks the log critical
        // and its range is kept for the chart.
        $log = $this->machine->logs()->sole();
        $this->assertSame('critical', $log->severity);
        $this->assertEquals(120, $log->reading_stats['temperature']['max']);
    }

    public function test_a_device_can_post_far_more_often_than_the_old_per_ip_limit(): void
    {
        foreach (range(1, 130) as $i) {
            $this->sendReading(['temperature' => 70])->assertCreated();
        }

        $this->assertEquals(130, $this->machine->logs()->sole()->reading_stats['temperature']['n']);
    }

    public function test_the_rate_limit_is_per_device_not_per_ip(): void
    {
        $other = Machine::factory()->create();

        $limiter = RateLimiter::limiter('device-api');

        $request = fn (Machine $machine) => tap(Request::create('/api/machine-logs', 'POST', server: ['REMOTE_ADDR' => '10.0.0.5']), fn (Request $r) => $r->attributes->set('device_machine', $machine));

        $first = $limiter($request($this->machine));
        $second = $limiter($request($other));

        $this->assertNotSame($first->key, $second->key);
        $this->assertSame(1200, $first->maxAttempts);
    }
}
