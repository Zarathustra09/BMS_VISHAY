<?php

namespace App\Interfaces;

interface ReadingRollupInterface
{
    /**
     * Summarise newly stored sensor readings into per-minute rows (machine_reading_rollups).
     *
     * Reads machine_logs rows added since the stored cursor, then recomputes every minute they
     * touch from all of that minute's rows, so a minute that a replayed old reading lands in is
     * corrected, and re-rolls the last couple of minutes to catch a per-second row that kept
     * growing. Idempotent: running it twice changes nothing.
     *
     * @return array{rows: int, minutes: int, cursor: int} new logs read, minutes recomputed, cursor after
     */
    public function rollUp(int $limit = 50000): array;

    /**
     * The highest machine_logs id already summarised. A detailed log at or below it is safe to prune.
     */
    public function cursor(): int;
}
