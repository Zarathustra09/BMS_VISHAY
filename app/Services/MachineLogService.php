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
        '1h' => ['seconds' => 3600,   'bucket' => 60,   'format' => 'H:i'],
        '24h' => ['seconds' => 86400,  'bucket' => 900,  'format' => 'H:i'],
        '7d' => ['seconds' => 604800, 'bucket' => 7200, 'format' => 'M j H:i'],
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

    public function getReadingSeries(Machine $machine, ?string $metric = null, string $range = '24h'): array
    {
        $range = isset(self::RANGES[$range]) ? $range : '24h';
        $cfg = self::RANGES[$range];

        // Only a number parameter can be charted. Offered in the machine's own display order,
        // so the first one is the chart's default.
        $available = $machine->parameters()->where('data_type', 'number')->get()->keyBy('key');

        $parameter = ($metric !== null ? $available->get($metric) : null) ?? $available->first();

        $points = [];
        $bucketSeconds = $cfg['bucket'];

        if ($parameter) {
            // The key comes from the machine's own parameter row (never from the request), and
            // can only contain [a-z0-9_] — see MachineController's `parameters.*.key` rule.
            $column = "readings->{$parameter->key}";

            // ->toBase() skips Eloquent hydration and the JSON cast — we only need logged_at
            // plus one value, which MySQL returns as a string and SQLite as a number.
            $rows = MachineLog::query()
                ->toBase()
                ->where('machine_id', $machine->id)
                ->where('logged_at', '>=', now()->subSeconds($cfg['seconds']))
                ->whereNotNull($column)
                ->orderByDesc('logged_at')
                ->limit(self::MAX_RAW_ROWS)
                ->get(['logged_at', "{$column} as reading"]);

            $buckets = [];

            foreach ($rows as $row) {
                // strtotime(), not Carbon::parse() — only epoch seconds are needed for
                // bucketing, and avoiding a Carbon instantiation per row matters at this volume.
                $ts = strtotime($row->logged_at);
                $key = intdiv($ts, $bucketSeconds) * $bucketSeconds;
                $value = (float) $row->reading;

                if (! isset($buckets[$key])) {
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

        $bound = fn (?string $column) => $parameter && $parameter->{$column} !== null ? (float) $parameter->{$column} : null;

        return [
            'metric' => $parameter?->key,
            'label' => $parameter?->labelWithUnit(),
            'range' => $range,
            'bucket_seconds' => $bucketSeconds,
            'available_metrics' => $available->map(fn ($p) => ['value' => $p->key, 'label' => $p->labelWithUnit()])->values()->all(),
            'points' => $points,
            'thresholds' => [
                'warning_min' => $bound('warning_min'),
                'warning_max' => $bound('warning_max'),
                'critical_min' => $bound('critical_min'),
                'critical_max' => $bound('critical_max'),
            ],
            'latest' => $points ? end($points)['avg'] : null,
            'online' => $machine->isOnline(AlertInterface::OFFLINE_AFTER_MINUTES),
            'last_seen_at' => $machine->last_seen_at?->toIso8601String(),
            'last_seen_human' => $machine->last_seen_at?->diffForHumans(),
            'offline_after' => AlertInterface::OFFLINE_AFTER_MINUTES,
        ];
    }
}
