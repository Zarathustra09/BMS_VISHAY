<?php

namespace App\Jobs;

use App\Interfaces\AIInterface;
use App\Models\Machine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessMachineAIInsight implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    protected int $machineId;
    protected string $userMessage;
    protected string $cacheKey;

    /**
     * Create a new job instance.
     */
    public function __construct(int $machineId, string $userMessage, string $cacheKey)
    {
        $this->machineId = $machineId;
        $this->userMessage = $userMessage;
        $this->cacheKey = $cacheKey;
    }

    /**
     * Execute the job.
     */
    public function handle(AIInterface $aiService): void
    {
        try {
            $machine = Machine::findOrFail($this->machineId);

            $response = $aiService->generateInsights($machine, $this->userMessage);

            // Store the response in cache for retrieval
            Cache::put($this->cacheKey, [
                'ready' => true,
                'message' => $response,
                'generated_at' => now()->toIso8601String(),
            ], now()->addHours(1));

            Log::info('AI insight generated successfully', [
                'machine_id' => $this->machineId,
                'cache_key' => $this->cacheKey,
            ]);
        } catch (\Exception $e) {
            Log::error('AI insight generation failed', [
                'machine_id' => $this->machineId,
                'error' => $e->getMessage(),
            ]);

            // Store error in cache
            Cache::put($this->cacheKey, [
                'ready' => true,
                'message' => '❌ Sorry, I encountered an error while analyzing the machine data. Please try again.',
                'error' => true,
            ], now()->addMinutes(5));
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Cache::put($this->cacheKey, [
            'ready' => true,
            'message' => '❌ The analysis request failed after multiple attempts. Please try again later.',
            'error' => true,
        ], now()->addMinutes(5));

        Log::error('AI insight job failed', [
            'machine_id' => $this->machineId,
            'cache_key' => $this->cacheKey,
            'error' => $exception->getMessage(),
        ]);
    }
}

