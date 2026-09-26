<?php

namespace App\Services;

use App\Interfaces\AlertInterface;
use App\Interfaces\MachineLogInterface;
use App\Models\Machine;
use App\Models\MachineLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class MachineLogService implements MachineLogInterface
{
    /**
     * Range key => [window in seconds, bucket size in seconds, label format]. Bucket sizes
     * target 60-100 points per range — dense enough to look like a chart, sparse enough for
     * a hover crosshair to feel precise.
     */
    private const RANGES = [
        '1h'  => ['seconds' => 3600,   'bucket' => 60,   'format' => 'H:i'],
        '24h' => ['seconds' => 86400,  'bucket' => 900,  'format' => 'H:i'],
        '7d'  => ['seconds' => 604800, 'bucket' => 7200, 'format' => 'M j H:i'],
    ];

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

        return $log;
    }

    public function getQueryForDataTable(Machine $machine, array $filters = []): Builder
    {
        $query = MachineLog::query()->where('machine_id', $machine->id);

        if (!empty($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (!empty($filters['log_type'])) {
            $query->where('log_type', $filters['log_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('logged_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('logged_at', '<=', $filters['date_to']);
        }

        return $query->orderByDesc('logged_at');
    }

    public function getReadingSeries(Machine $machine, ?string $metric = null, string $range = '24h'): array
    {
        $range = isset(self::RANGES[$range]) ? $range : '24h';
        $cfg = self::RANGES[$range];

        $available = $this->availableMetrics($machine);

        if ($metric === null || !isset($available[$metric])) {
            $metric = array_key_first($available);
        }

        $points = [];
        $bucketSeconds = $cfg['bucket'];

        if ($metric !== null) {
            // ->toBase() skips Eloquent hydration and the decimal:N cast (which would turn
            // every value into a string) — we only need logged_at + one numeric column.
            $rows = MachineLog::query()
                ->toBase()
                ->where('machine_id', $machine->id)
                ->where('logged_at', '>=', now()->subSeconds($cfg['seconds']))
                ->whereNotNull($metric)
                ->orderByDesc('logged_at')
                ->limit(self::MAX_RAW_ROWS)
                ->get(['logged_at', $metric]);

            $buckets = [];

            foreach ($rows as $row) {
                // strtotime(), not Carbon::parse() — only epoch seconds are needed for
                // bucketing, and avoiding a Carbon instantiation per row matters at this volume.
                $ts = strtotime($row->logged_at);
                $key = intdiv($ts, $bucketSeconds) * $bucketSeconds;
                $value = (float) $row->{$metric};

                if (!isset($buckets[$key])) {
                    $buckets[$key] = ['sum' => 0.0, 'n' => 0, 'min' => $value, 'max' => $value];
                }

                $buckets[$key]['sum'] += $value;
                $buckets[$key]['n']++;
                $buckets[$key]['min'] = min($buckets[$key]['min'], $value);
                $buckets[$key]['max'] = max($buckets[$key]['max'], $value);
            }

            ksort($buckets);

            // Empty buckets (a silent period) are deliberately NOT zero-filled — the chart
            // breaks the line across the gap instead, which is how an outage should read.
            foreach ($buckets as $key => $bucket) {
                $points[] = [
                    't' => $key,
                    'avg' => round($bucket['sum'] / $bucket['n'], 3),
                    'min' => round($bucket['min'], 3),
                    'max' => round($bucket['max'], 3),
                    'n' => $bucket['n'],
                    'label' => Carbon::createFromTimestamp($key)->format($cfg['format']),
                ];
            }
        }

        $threshold = $metric ? $this->alerts->getThresholds($machine)->get($metric) : null;

        return [
            'metric' => $metric,
            'label' => $metric ? (AlertInterface::METRIC_LABELS[$metric] ?? $metric) : null,
            'range' => $range,
            'bucket_seconds' => $bucketSeconds,
            'available_metrics' => array_map(
                fn ($m) => ['value' => $m, 'label' => AlertInterface::METRIC_LABELS[$m] ?? $m],
                array_keys($available)
            ),
            'points' => $points,
            'thresholds' => [
                'warning_min' => $threshold?->warning_min !== null ? (float) $threshold->warning_min : null,
                'warning_max' => $threshold?->warning_max !== null ? (float) $threshold->warning_max : null,
                'critical_min' => $threshold?->critical_min !== null ? (float) $threshold->critical_min : null,
                'critical_max' => $threshold?->critical_max !== null ? (float) $threshold->critical_max : null,
            ],
            'latest' => $points ? end($points)['avg'] : null,
            'online' => $machine->isOnline(AlertInterface::OFFLINE_AFTER_MINUTES),
            'last_seen_at' => $machine->last_seen_at?->toIso8601String(),
            'last_seen_human' => $machine->last_seen_at?->diffForHumans(),
            'offline_after' => AlertInterface::OFFLINE_AFTER_MINUTES,
        ];
    }

    /**
     * Which of the monitorable metrics this machine actually has data for, ordered by count
     * desc so array_key_first() is the sensible chart default. Scoped to the widest range the
     * chart offers (7d) so the dropdown never offers a metric that would draw nothing, and so
     * this stays inside the (machine_id, logged_at) index rather than scanning the machine's
     * whole log history. COUNT(col) counts non-nulls identically on MySQL and SQLite.
     */
    private function availableMetrics(Machine $machine): array
    {
        $selects = array_map(fn ($m) => "COUNT({$m}) as {$m}", AlertInterface::METRICS);

        $row = MachineLog::query()
            ->toBase()
            ->where('machine_id', $machine->id)
            ->where('logged_at', '>=', now()->subSeconds(self::RANGES['7d']['seconds']))
            ->selectRaw(implode(', ', $selects))
            ->first();

        $counts = array_filter((array) $row, fn ($count) => $count > 0);
        arsort($counts);

        return $counts;
    }
}
