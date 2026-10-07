<?php

namespace App\Services;

use App\Events\MachineReadingRecorded;
use App\Interfaces\AlertInterface;
use App\Interfaces\MachineLogInterface;
use App\Models\Machine;
use App\Models\MachineLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class MachineLogService implements MachineLogInterface
{
    /**
     * Preset range key => window length in seconds — MachineLogInterface::RANGE_PRESETS, in
     * ascending order, which resolveRange() relies on to pick the smallest window that holds
     * the latest reading.
     */
    private const RANGES = [
        '5m' => 300,
        '15m' => 900,
        '1h' => 3600,
        '6h' => 21600,
        '24h' => 86400,
        '7d' => 604800,
        '30d' => 2592000,
    ];

    /**
     * At or below this many readings in the window, every reading is its own chart point
     * ("raw" mode) — a 1-minute test burst, or a device reporting every few minutes, is shown
     * exactly as sent instead of being averaged into one or two buckets.
     */
    private const RAW_POINT_LIMIT = 300;

    /**
     * Above RAW_POINT_LIMIT, readings are averaged into the smallest of these bucket sizes
     * (seconds) that keeps the chart to about TARGET_POINTS points over the span the data
     * actually covers — not the window's span, so a dense burst inside a 7-day window still
     * gets fine resolution.
     */
    private const BUCKET_SIZES = [1, 5, 10, 30, 60, 300, 600, 900, 1800, 3600, 7200, 21600, 43200, 86400];

    private const TARGET_POINTS = 150;

    /**
     * How often (seconds) the chart re-polls for a window that is still "live", by window
     * length: a short window is usually someone watching a test, a long one changes slowly.
     */
    private const POLL_SECONDS = [900 => 5, 3600 => 10];

    private const DEFAULT_POLL_SECONDS = 30;

    /**
     * Set for BROADCAST_PAUSE_SECONDS after a failed live broadcast, so a stopped Reverb costs
     * a device's POST one failed attempt per half minute instead of one on every reading.
     */
    private const BROADCAST_DOWN_KEY = 'live-broadcast-down';

    private const BROADCAST_PAUSE_SECONDS = 30;

    /**
     * A firehose machine (many readings/second) could return hundreds of thousands of rows
     * for a 7-day window — cap it and lose the oldest part of the window rather than the
     * request ballooning memory. Rows are fetched newest-first so the cap keeps the most
     * recent readings; final point order is restored by ksort()'ing the buckets afterward.
     */
    private const MAX_RAW_ROWS = 20000;

    public function __construct(protected AlertInterface $alerts) {}

    public function store(array $data): MachineLog
    {
        if (empty($data['logged_at'])) {
            $data['logged_at'] = now();
        }

        $log = MachineLog::create($data);

        // now(), deliberately NOT $log->logged_at: a device replaying a buffer of old
        // readings (see docs/05-device-api.md) has just proven it is alive right now.
        // last_seen_at means "when did we last hear from this device", not "how fresh is
        // the newest measurement". Kept here (not the API controller) so every caller of
        // store() — not just the sensor API — keeps this in sync with offline detection.
        $log->machine?->forceFill(['last_seen_at' => now()])->save();

        $this->alerts->evaluate($log);

        if (! empty($log->readings)) {
            $this->broadcastReading($log);
        }

        return $log;
    }

    /**
     * Live push to anyone watching this machine (Laravel Reverb). Reverb is strictly an add-on:
     * a stopped or unreachable server can never turn a device's already-stored reading into a
     * 500 — the chart just falls back to polling. A failure also opens a short circuit breaker
     * (BROADCAST_DOWN_KEY) so the next readings skip the attempt rather than each waiting out
     * the connect timeout, and logs one line rather than a stack trace per reading.
     */
    private function broadcastReading(MachineLog $log): void
    {
        if (Cache::has(self::BROADCAST_DOWN_KEY)) {
            return;
        }

        rescue(fn () => event(new MachineReadingRecorded($log)), function (Throwable $e) {
            Cache::put(self::BROADCAST_DOWN_KEY, true, self::BROADCAST_PAUSE_SECONDS);

            Log::warning('Live broadcast failed (is Reverb running?); readings are still stored and the chart falls back to polling. '.$e->getMessage());
        }, report: false);
    }

    public function getQueryForDataTable(Machine $machine, array $filters = []): Builder
    {
        $query = MachineLog::query()->where('machine_id', $machine->id);

        if (! empty($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (! empty($filters['log_type'])) {
            $query->where('log_type', $filters['log_type']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('logged_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('logged_at', '<=', $filters['date_to']);
        }

        return $query->orderByDesc('logged_at');
    }

    public function getReadingSeries(Machine $machine, ?string $metric = null, string $range = 'auto', ?Carbon $from = null, ?Carbon $to = null): array
    {
        // Only a number parameter can be charted. Offered in the machine's own display order,
        // so the first one is the chart's default.
        $available = $machine->parameters()->where('data_type', 'number')->get()->keyBy('key');

        $parameter = ($metric !== null ? $available->get($metric) : null) ?? $available->first();

        // The key comes from the machine's own parameter row (never from the request), and
        // can only contain [a-z0-9_] — see MachineController's `parameters.*.key` rule.
        $column = $parameter ? "readings->{$parameter->key}" : null;

        $latestReadingAt = $column ? $this->latestReadingAt($machine, $column) : null;

        [$resolved, $windowStart, $windowEnd] = $this->resolveRange($range, $from, $to, $latestReadingAt);

        $rows = $column ? $this->fetchRows($machine, $column, $windowStart, $windowEnd, $resolved === 'custom') : [];

        [$points, $resolution, $gapSeconds] = $this->buildPoints($rows);

        $bound = fn (?string $name) => $parameter && $parameter->{$name} !== null ? (float) $parameter->{$name} : null;

        return [
            'metric' => $parameter?->key,
            'label' => $parameter?->labelWithUnit(),
            'requested_range' => $range,
            'range' => $resolved,
            'window_start' => $windowStart->toIso8601String(),
            'window_end' => $windowEnd->toIso8601String(),
            'window_start_input' => $windowStart->format('Y-m-d\TH:i'),
            'window_end_input' => $windowEnd->format('Y-m-d\TH:i'),
            'resolution' => $resolution,
            'reading_count' => count($rows),
            'gap_seconds' => $gapSeconds,
            'poll_seconds' => $this->pollSeconds($resolved, $windowStart, $windowEnd),
            'available_metrics' => $available->map(fn ($p) => ['value' => $p->key, 'label' => $p->labelWithUnit()])->values()->all(),
            'points' => $points,
            'thresholds' => [
                'warning_min' => $bound('warning_min'),
                'warning_max' => $bound('warning_max'),
                'critical_min' => $bound('critical_min'),
                'critical_max' => $bound('critical_max'),
            ],
            'latest' => $points ? end($points)['avg'] : null,
            'latest_reading_at' => $latestReadingAt?->toIso8601String(),
            'latest_reading_human' => $latestReadingAt?->diffForHumans(),
            'latest_reading_from_input' => $latestReadingAt?->copy()->subHour()->format('Y-m-d\TH:i'),
            'latest_reading_to_input' => $latestReadingAt?->copy()->addHour()->format('Y-m-d\TH:i'),
            'online' => $machine->isOnline(AlertInterface::OFFLINE_AFTER_MINUTES),
            'last_seen_at' => $machine->last_seen_at?->toIso8601String(),
            'last_seen_human' => $machine->last_seen_at?->diffForHumans(),
            'offline_after' => AlertInterface::OFFLINE_AFTER_MINUTES,
        ];
    }

    /**
     * When the parameter last reported, regardless of the chart's window — what lets an empty
     * window offer a "show the latest reading" shortcut, and what the `auto` range keys off.
     */
    private function latestReadingAt(Machine $machine, string $column): ?Carbon
    {
        $latest = MachineLog::query()
            ->toBase()
            ->where('machine_id', $machine->id)
            ->whereNotNull($column)
            ->orderByDesc('logged_at')
            ->value('logged_at');

        return $latest ? Carbon::parse($latest) : null;
    }

    /**
     * Turns the requested range into [resolved key, window start, window end]. `custom` uses
     * the explicit $from/$to. `auto` is the smallest preset whose window contains the latest
     * reading — so opening the page right after a 1-minute test lands on "5m" — falling back
     * to 24h with no readings at all and to 30d when the newest one is older than every
     * preset. An unknown key is treated as 24h, as the old fixed ranges did.
     *
     * @return array{0: string, 1: Carbon, 2: Carbon}
     */
    private function resolveRange(string $range, ?Carbon $from, ?Carbon $to, ?Carbon $latestReadingAt): array
    {
        if ($range === 'custom' && $from && $to) {
            return ['custom', $from, $to];
        }

        if ($range === 'auto') {
            $range = '24h';

            if ($latestReadingAt) {
                $range = array_key_last(self::RANGES);

                foreach (self::RANGES as $key => $seconds) {
                    if ($latestReadingAt->greaterThanOrEqualTo(now()->subSeconds($seconds))) {
                        $range = $key;

                        break;
                    }
                }
            }
        }

        if (! isset(self::RANGES[$range])) {
            $range = '24h';
        }

        return [$range, now()->subSeconds(self::RANGES[$range]), now()];
    }

    /**
     * The window's readings as [epoch seconds, value] pairs, oldest first. ->toBase() skips
     * Eloquent hydration and the JSON cast — only logged_at and one value are needed, which
     * MySQL returns as a string and SQLite as a number. Fetched newest-first so the
     * MAX_RAW_ROWS cap keeps the most recent readings.
     *
     * @return list<array{0: int, 1: float}>
     */
    private function fetchRows(Machine $machine, string $column, Carbon $start, Carbon $end, bool $bounded): array
    {
        $query = MachineLog::query()
            ->toBase()
            ->where('machine_id', $machine->id)
            ->where('logged_at', '>=', $start)
            ->whereNotNull($column)
            ->orderByDesc('logged_at')
            ->limit(self::MAX_RAW_ROWS);

        // A preset window runs up to "now", so it needs no upper bound; a custom one does.
        if ($bounded) {
            $query->where('logged_at', '<=', $end);
        }

        $rows = [];

        foreach ($query->get(['logged_at', "{$column} as reading"]) as $row) {
            // strtotime(), not Carbon::parse() — only epoch seconds are needed, and avoiding a
            // Carbon instantiation per row matters at this volume.
            $rows[] = [strtotime($row->logged_at), (float) $row->reading];
        }

        usort($rows, fn ($a, $b) => $a[0] <=> $b[0]);

        return $rows;
    }

    /**
     * Raw points when there are few enough readings, otherwise averaged buckets.
     *
     * Empty buckets (a silent period) are deliberately NOT zero-filled — the chart breaks the
     * line across the gap instead, which is how an outage should read; `gap_seconds` is how
     * wide a hole has to be before it does.
     *
     * @param  list<array{0: int, 1: float}>  $rows
     * @return array{0: list<array<string, mixed>>, 1: string, 2: int}
     */
    private function buildPoints(array $rows): array
    {
        if ($rows === []) {
            return [[], 'No readings', 5];
        }

        $span = $rows[count($rows) - 1][0] - $rows[0][0];

        // Label precision follows the data's own span, not the window's: a 1-minute burst
        // inside a 24h window still needs seconds to tell its points apart.
        $format = $span <= 3600 ? 'H:i:s' : ($span <= 86400 ? 'H:i' : 'M j H:i');

        if (count($rows) <= self::RAW_POINT_LIMIT) {
            $points = array_map(fn ($row) => [
                't' => $row[0],
                'avg' => round($row[1], 3),
                'min' => round($row[1], 3),
                'max' => round($row[1], 3),
                'n' => 1,
                'label' => Carbon::createFromTimestamp($row[0])->format($format),
            ], $rows);

            return [$points, 'Raw readings', $this->rawGapSeconds($rows)];
        }

        $bucketSeconds = $this->bucketSizeFor($span);
        $buckets = [];

        foreach ($rows as [$ts, $value]) {
            $key = intdiv($ts, $bucketSeconds) * $bucketSeconds;

            if (! isset($buckets[$key])) {
                $buckets[$key] = ['sum' => 0.0, 'n' => 0, 'min' => $value, 'max' => $value];
            }

            $buckets[$key]['sum'] += $value;
            $buckets[$key]['n']++;
            $buckets[$key]['min'] = min($buckets[$key]['min'], $value);
            $buckets[$key]['max'] = max($buckets[$key]['max'], $value);
        }

        ksort($buckets);

        $points = [];

        foreach ($buckets as $key => $bucket) {
            $points[] = [
                't' => $key,
                'avg' => round($bucket['sum'] / $bucket['n'], 3),
                'min' => round($bucket['min'], 3),
                'max' => round($bucket['max'], 3),
                'n' => $bucket['n'],
                'label' => Carbon::createFromTimestamp($key)->format($format),
            ];
        }

        return [$points, $this->bucketLabel($bucketSeconds), (int) ($bucketSeconds * 1.5)];
    }

    /**
     * The smallest bucket that keeps a data span to about TARGET_POINTS points (the largest if
     * none does).
     */
    private function bucketSizeFor(int $span): int
    {
        foreach (self::BUCKET_SIZES as $size) {
            if ($span / $size <= self::TARGET_POINTS) {
                return $size;
            }
        }

        return end(self::BUCKET_SIZES);
    }

    private function bucketLabel(int $seconds): string
    {
        return match (true) {
            $seconds < 60 => "{$seconds}-second averages",
            $seconds < 3600 => ($seconds / 60).'-minute averages',
            $seconds < 86400 => ($seconds / 3600).'-hour averages',
            default => ($seconds / 86400).'-day averages',
        };
    }

    /**
     * How big a hole between two raw points breaks the line: three times the usual interval
     * between readings, but never under 5 seconds (a burst sent every second shouldn't be
     * broken by one slow request).
     *
     * @param  list<array{0: int, 1: float}>  $rows
     */
    private function rawGapSeconds(array $rows): int
    {
        $intervals = [];

        for ($i = 1; $i < count($rows); $i++) {
            $intervals[] = $rows[$i][0] - $rows[$i - 1][0];
        }

        if ($intervals === []) {
            return 5;
        }

        sort($intervals);

        return max(5, (int) ($intervals[intdiv(count($intervals), 2)] * 3));
    }

    /**
     * Seconds between the chart's live polls, or null when nothing new can arrive — a custom
     * window that ended in the past.
     */
    private function pollSeconds(string $resolved, Carbon $start, Carbon $end): ?int
    {
        if ($resolved === 'custom' && $end->lessThan(now()->subMinute())) {
            return null;
        }

        $length = $start->diffInSeconds($end, true);

        foreach (self::POLL_SECONDS as $upTo => $seconds) {
            if ($length <= $upTo) {
                return $seconds;
            }
        }

        return self::DEFAULT_POLL_SECONDS;
    }
}
