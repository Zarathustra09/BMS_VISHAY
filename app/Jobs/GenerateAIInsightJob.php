<?php

namespace App\Jobs;

use App\Models\Machine;
use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateAIInsightJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $machineId;
    protected $question;
    protected $cacheKey;

    public $timeout = 60;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(int $machineId, string $question, string $cacheKey)
    {
        $this->machineId = $machineId;
        $this->question = $question;
        $this->cacheKey = $cacheKey;
    }

    /**
     * Execute the job.
     */
    public function handle(GeminiService $geminiService): void
    {
        $jobStartTime = microtime(true);

        Log::info('AI Insight Job Started', [
            'job_id' => $this->job->getJobId(),
            'machine_id' => $this->machineId,
            'question' => $this->question,
            'cache_key' => $this->cacheKey,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Set processing status
            Cache::put($this->cacheKey . '_status', 'processing', now()->addMinutes(5));

            $machine = Machine::with(['logs' => function($query) {
                $query->orderBy('logged_at', 'desc')->limit(50);
            }])->findOrFail($this->machineId);

            Log::debug('Machine Data Loaded', [
                'machine_id' => $this->machineId,
                'machine_code' => $machine->machine_code,
                'logs_loaded' => $machine->logs->count(),
            ]);

            // Prepare machine data
            $machineData = [
                'name' => $machine->name,
                'machine_code' => $machine->machine_code,
                'type' => $machine->type,
                'status' => $machine->status,
                'location' => $machine->location,
                'power_rating' => $machine->power_rating,
                'manufacturer' => $machine->manufacturer,
            ];

            // Prepare logs data
            $logsData = [
                'total' => $machine->logs()->count(),
                'critical' => $machine->logs()->where('severity', 'critical')->count(),
                'errors' => $machine->logs()->where('severity', 'error')->count(),
                'warnings' => $machine->logs()->where('severity', 'warning')->count(),
                'today' => $machine->logs()->whereDate('logged_at', today())->count(),
                'week' => $machine->logs()->where('logged_at', '>=', now()->subWeek())->count(),
                'recent_logs' => $machine->logs->map(function($log) {
                    return [
                        'severity' => $log->severity,
                        'event' => $log->event,
                        'logged_at' => $log->logged_at->format('Y-m-d H:i:s'),
                        'temperature' => $log->temperature,
                        'power_consumption' => $log->power_consumption,
                        'vibration_rms' => $log->vibration_rms,
                        'pressure' => $log->pressure,
                    ];
                })->toArray(),
            ];

            Log::debug('Logs Data Prepared', [
                'machine_id' => $this->machineId,
                'total_logs' => $logsData['total'],
                'critical_logs' => $logsData['critical'],
                'recent_logs_count' => count($logsData['recent_logs']),
            ]);

            // Build prompt and generate content
            $prompt = $geminiService->buildAnalysisPrompt($machineData, $logsData, $this->question);

            Log::info('Calling Gemini API', [
                'machine_id' => $this->machineId,
                'question' => $this->question,
                'prompt_length' => strlen($prompt),
            ]);

            $result = $geminiService->generateContent($prompt);

            $jobDuration = microtime(true) - $jobStartTime;

            Log::info('Gemini API Call Completed', [
                'machine_id' => $this->machineId,
                'success' => $result['success'],
                'response_length' => isset($result['text']) ? strlen($result['text']) : 0,
                'job_duration_seconds' => round($jobDuration, 2),
            ]);

            // Cache the result
            Cache::put($this->cacheKey, $result, now()->addMinutes(10));
            Cache::forget($this->cacheKey . '_status');

            Log::info('AI Insight Generated Successfully', [
                'job_id' => $this->job->getJobId(),
                'machine_id' => $this->machineId,
                'machine_code' => $machine->machine_code,
                'question' => $this->question,
                'success' => $result['success'],
                'cached' => true,
                'cache_key' => $this->cacheKey,
                'total_duration_seconds' => round($jobDuration, 2),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $jobDuration = microtime(true) - $jobStartTime;

            Log::error('AI Insight Job Failed - Machine Not Found', [
                'job_id' => $this->job->getJobId(),
                'machine_id' => $this->machineId,
                'question' => $this->question,
                'error' => $e->getMessage(),
                'duration_seconds' => round($jobDuration, 2),
            ]);

            Cache::put($this->cacheKey, [
                'success' => false,
                'text' => 'Machine not found. Please try again.'
            ], now()->addMinutes(5));

            Cache::forget($this->cacheKey . '_status');

        } catch (\Exception $e) {
            $jobDuration = microtime(true) - $jobStartTime;

            Log::error('AI Insight Job Failed', [
                'job_id' => $this->job->getJobId(),
                'machine_id' => $this->machineId,
                'question' => $this->question,
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'attempt' => $this->attempts(),
                'duration_seconds' => round($jobDuration, 2),
                'trace' => $e->getTraceAsString(),
            ]);

            Cache::put($this->cacheKey, [
                'success' => false,
                'text' => 'Failed to generate insight. Please try again later.'
            ], now()->addMinutes(5));

            Cache::forget($this->cacheKey . '_status');

            // Re-throw if we want to retry
            if ($this->attempts() < $this->tries) {
                throw $e;
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AI Insight Job Failed Permanently', [
            'machine_id' => $this->machineId,
            'question' => $this->question,
            'cache_key' => $this->cacheKey,
            'attempts' => $this->attempts(),
            'exception' => $exception->getMessage(),
        ]);

        Cache::put($this->cacheKey, [
            'success' => false,
            'text' => 'Failed to generate insight after multiple attempts. Please contact support.'
        ], now()->addMinutes(5));

        Cache::forget($this->cacheKey . '_status');
    }
}
