<?php

namespace App\Services;

use App\Interfaces\ReadingRollupInterface;
use App\Models\MachineLog;
use App\Models\MachineParameter;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReadingRollupService implements ReadingRollupInterface
{
    private const CURSOR_KEY = 'readings-rollup-cursor';

    /**
     * The newest minutes are always recomputed: a fast device's per-second row keeps growing
     * after it was first read, and a row that committed late can have an id below the cursor.
     */
    private const RECENT_MINUTES = 2;

    /**
     * Minutes closer together than this are read with one query when they have to be recomputed
     * (a replayed buffer touches many neighbouring minutes).
     */
    private const RANGE_GAP_MINUTES = 10;

    public function cursor(): int
    {
        return (int) Cache::get(self::CURSOR_KEY, 0);
    }

    public function rollUp(int $limit = 50000): array
    {
        $cursor = $this->cursor();

        $fresh = $this->sensorLogs()
            ->where('id', '>', $cursor)
            ->orderBy('id')
            ->limit($limit)
            ->get(['id', 'machine_id', 'logged_at']);

        $recent = $this->sensorLogs()
            ->where('logged_at', '>=', now()->subMinutes(self::RECENT_MINUTES)->startOfMinute())
            ->get(['machine_id', 'logged_at']);

        // machine id => [minute start => true]
        $affected = [];

        foreach ($fresh->concat($recent) as $row) {
            $affected[$row->machine_id][$this->minuteOf($row->logged_at)] = true;
        }

        $minutes = 0;

        foreach ($affected as $machineId => $starts) {
            $starts = array_keys($starts);
            sort($starts);

            $minutes += count($starts);
            $this->summarise((int) $machineId, $starts);
        }

        $newCursor = max($cursor, (int) ($fresh->max('id') ?? 0));

        if ($newCursor !== $cursor) {
            Cache::forever(self::CURSOR_KEY, $newCursor);
        }

        return ['rows' => $fresh->count(), 'minutes' => $minutes, 'cursor' => $newCursor];
    }

    /**
     * Plain query builder (no hydration, no JSON cast): only a few columns are needed per row
     * and this is read at volume.
     */
    private function sensorLogs(): Builder
    {
        return MachineLog::query()
            ->toBase()
            ->where('log_type', 'sensor_reading')
            ->whereNotNull('readings');
    }

    private function minuteOf(string $loggedAt): int
    {
        $timestamp = strtotime($loggedAt);

        return $timestamp - ($timestamp % 60);
    }

    /**
     * Recompute these minutes (sorted, for one machine) from their logs and upsert the result.
     *
     * @param  list<int>  $starts
     */
    private function summarise(int $machineId, array $starts): void
    {
        $numberKeys = MachineParameter::query()
            ->where('machine_id', $machineId)
            ->where('data_type', 'number')
            ->pluck('key')
            ->flip()
            ->all();

        if ($numberKeys === []) {
            return;
        }

        $wanted = array_flip($starts);

        // metric => minute start => [min, max, sum, count]
        $sums = [];

        foreach ($this->ranges($starts) as [$from, $to]) {
            $rows = $this->sensorLogs()
                ->where('machine_id', $machineId)
                ->where('logged_at', '>=', Carbon::createFromTimestamp($from))
                ->where('logged_at', '<', Carbon::createFromTimestamp($to + 60))
                ->get(['readings', 'reading_stats', 'logged_at']);

            foreach ($rows as $row) {
                $minute = $this->minuteOf($row->logged_at);

                if (! isset($wanted[$minute])) {
                    continue;
                }

                $readings = json_decode($row->readings, true) ?: [];
                $stats = $row->reading_stats ? (json_decode($row->reading_stats, true) ?: []) : [];

                foreach ($readings as $key => $value) {
                    if (! isset($numberKeys[$key]) || ! is_numeric($value)) {
                        continue;
                    }

                    $value = (float) $value;
                    $stat = $stats[$key] ?? null;

                    // A merged per-second row: `readings` holds the average of `n` samples.
                    $n = is_array($stat) ? max(1, (int) ($stat['n'] ?? 1)) : 1;
                    $low = is_array($stat) && isset($stat['min']) ? (float) $stat['min'] : $value;
                    $high = is_array($stat) && isset($stat['max']) ? (float) $stat['max'] : $value;

                    if (! isset($sums[$key][$minute])) {
                        $sums[$key][$minute] = [$low, $high, 0.0, 0];
                    }

                    $bucket = &$sums[$key][$minute];
                    $bucket[0] = min($bucket[0], $low);
                    $bucket[1] = max($bucket[1], $high);
                    $bucket[2] += $value * $n;
                    $bucket[3] += $n;
                    unset($bucket);
                }
            }
        }

        $existing = $this->existingCounts($machineId, $starts);

        $payload = [];

        foreach ($sums as $metric => $perMinute) {
            foreach ($perMinute as $minute => [$low, $high, $sum, $count]) {
                // Rows pruned since this minute was first summarised would shrink it: keep the
                // bigger, complete summary. A genuine recompute never has fewer samples.
                if (($existing[$metric][$minute] ?? 0) > $count) {
                    continue;
                }

                $payload[] = [
                    'machine_id' => $machineId,
                    'metric' => $metric,
                    'period_start' => $minute,
                    'min' => $low,
                    'max' => $high,
                    'sum' => $sum,
                    'count' => $count,
                ];
            }
        }

        foreach (array_chunk($payload, 500) as $chunk) {
            DB::table('machine_reading_rollups')->upsert(
                $chunk,
                ['machine_id', 'metric', 'period_start'],
                ['min', 'max', 'sum', 'count']
            );
        }
    }

    /**
     * Group sorted minute starts into [from, to] ranges, so neighbouring minutes share a query.
     *
     * @param  list<int>  $starts
     * @return list<array{0: int, 1: int}>
     */
    private function ranges(array $starts): array
    {
        $ranges = [];
        $from = $to = $starts[0];

        foreach (array_slice($starts, 1) as $start) {
            if ($start - $to <= self::RANGE_GAP_MINUTES * 60) {
                $to = $start;

                continue;
            }

            $ranges[] = [$from, $to];
            $from = $to = $start;
        }

        $ranges[] = [$from, $to];

        return $ranges;
    }

    /**
     * @param  list<int>  $starts
     * @return array<string, array<int, int>> metric => minute start => stored sample count
     */
    private function existingCounts(int $machineId, array $starts): array
    {
        $counts = [];

        foreach (array_chunk($starts, 1000) as $chunk) {
            $rows = DB::table('machine_reading_rollups')
                ->where('machine_id', $machineId)
                ->whereIn('period_start', $chunk)
                ->get(['metric', 'period_start', 'count']);

            foreach ($rows as $row) {
                $counts[$row->metric][(int) $row->period_start] = (int) $row->count;
            }
        }

        return $counts;
    }
}
