<?php

namespace App\Console\Commands;

use App\Interfaces\ReadingRollupInterface;
use Illuminate\Console\Command;

class RollupReadings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:rollup-readings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Summarise new sensor readings into per-minute rows used by the long chart windows';

    public function handle(ReadingRollupInterface $rollups): int
    {
        $result = $rollups->rollUp();

        $this->info("Read {$result['rows']} new log(s), wrote summaries for {$result['minutes']} minute(s); cursor at {$result['cursor']}.");

        return self::SUCCESS;
    }
}
