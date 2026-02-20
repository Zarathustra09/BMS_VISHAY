<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunQueueWorker extends Command
{
    protected $signature = 'app:run-queue-worker';
    protected $description = 'Run the queue worker';

    public function handle()
    {
        \Log::info('Queue worker started at: ' . now()->toDateTimeString());
        $this->call('queue:work', ['--stop-when-empty' => true]);
        \Log::info('Queue worker finished at: ' . now()->toDateTimeString());
    }
}
