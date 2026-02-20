<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;

class ProcessQueue extends Command
{
    protected $signature = 'app:process-queue';
    protected $description = 'Process the email queue only if there are jobs waiting';

    public function handle()
    {
        // Check if there are any jobs in the queue
        $queueSize = Queue::size();

        if ($queueSize === 0) {
            $this->info('No jobs in queue. Skipping queue processing.');
            Log::channel('queue_processing')->info('Queue check: No jobs found at ' . now()->toDateTimeString());
            return 0;
        }

        Log::channel('queue_processing')->info("Queue processing started at: " . now()->toDateTimeString() . " - {$queueSize} jobs found");
        $this->info("Starting email queue processing... ({$queueSize} jobs found)");

        $this->call('queue:work', [
            '--stop-when-empty' => true,
            '--max-time' => 50,
            '--timeout' => 60
        ]);

        Log::channel('queue_processing')->info('Queue processing finished at: ' . now()->toDateTimeString());
        $this->info('Email queue processing completed.');

        return 0;
    }
}
