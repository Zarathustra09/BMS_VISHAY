<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAIInsight;
use App\Models\Machine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AIController extends Controller
{
    /**
     * Handle chat message and dispatch AI processing job
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'message' => 'required|string|max:1000',
        ]);

        $machineId = $request->input('machine_id');
        $userMessage = $request->input('message');

        // Generate a unique cache key for this request
        $cacheKey = 'ai_insight_' . Str::uuid()->toString();

        // Check if we have a recent cached response for similar queries
        $cachedResponseKey = 'ai_cache_' . md5($machineId . '_' . $userMessage);
        $cachedResponse = Cache::get($cachedResponseKey);

        if ($cachedResponse) {
            return response()->json([
                'success' => true,
                'cached' => true,
                'message' => $cachedResponse,
            ]);
        }

        // Initialize cache entry as processing
        Cache::put($cacheKey, [
            'ready' => false,
            'message' => null,
        ], now()->addHours(1));

        // Dispatch the job to the queue
        ProcessAIInsight::dispatch($machineId, $userMessage, $cacheKey);

        return response()->json([
            'success' => true,
            'processing' => true,
            'cache_key' => $cacheKey,
            'message' => 'Your request is being processed...',
        ]);
    }

    /**
     * Check the status of an AI processing request
     */
    public function checkStatus(Request $request): JsonResponse
    {
        $request->validate([
            'cache_key' => 'required|string',
        ]);

        $cacheKey = $request->input('cache_key');
        $cached = Cache::get($cacheKey);

        if (!$cached) {
            return response()->json([
                'ready' => false,
                'message' => 'Request not found or expired.',
            ]);
        }

        if ($cached['ready']) {
            // Cache the response for similar future queries if not an error
            if (!isset($cached['error'])) {
                // We don't have the original query here, so skip caching for similar queries
            }

            return response()->json([
                'ready' => true,
                'message' => $cached['message'],
            ]);
        }

        return response()->json([
            'ready' => false,
            'message' => 'Still processing...',
        ]);
    }

    /**
     * Get suggested questions for a machine
     */
    public function getSuggestedQuestions(Machine $machine): JsonResponse
    {
        $questions = $this->generateSuggestedQuestions($machine);

        return response()->json([
            'success' => true,
            'questions' => $questions,
        ]);
    }

    /**
     * Generate context-aware suggested questions based on machine state
     */
    protected function generateSuggestedQuestions(Machine $machine): array
    {
        $questions = [];

        // Always include general health question
        $questions[] = [
            'icon' => '🔍',
            'question' => 'What is the overall health status of this machine?',
        ];

        // Check for recent critical alerts
        $criticalCount = $machine->logs()
            ->where('severity', 'critical')
            ->where('logged_at', '>=', now()->subWeek())
            ->count();

        if ($criticalCount > 0) {
            $questions[] = [
                'icon' => '🚨',
                'question' => 'Analyze the recent critical alerts and suggest solutions',
            ];
        }

        // Check maintenance status
        if ($machine->next_maintenance_date) {
            $daysUntil = now()->diffInDays($machine->next_maintenance_date, false);
            if ($daysUntil < 0) {
                $questions[] = [
                    'icon' => '🔧',
                    'question' => 'What maintenance tasks are overdue and what are the risks?',
                ];
            } elseif ($daysUntil <= 7) {
                $questions[] = [
                    'icon' => '📅',
                    'question' => 'What should be checked in the upcoming maintenance?',
                ];
            }
        }

        // Add sensor-related questions based on available data
        $recentLog = $machine->logs()->latest('logged_at')->first();

        if ($recentLog) {
            if ($recentLog->temperature !== null) {
                $questions[] = [
                    'icon' => '🌡️',
                    'question' => 'Are there any temperature anomalies or trends?',
                ];
            }

            if ($recentLog->power_consumption !== null) {
                $questions[] = [
                    'icon' => '⚡',
                    'question' => 'Analyze power consumption patterns and efficiency',
                ];
            }

            if ($recentLog->vibration_rms !== null) {
                $questions[] = [
                    'icon' => '📊',
                    'question' => 'Are vibration levels within normal range?',
                ];
            }
        }

        // Add performance question
        $questions[] = [
            'icon' => '📈',
            'question' => 'What optimizations can improve this machine\'s performance?',
        ];

        // Add risk assessment for critical machines
        if ($machine->is_critical) {
            $questions[] = [
                'icon' => '⚠️',
                'question' => 'What are the current risk factors for this critical system?',
            ];
        }

        // Limit to 6 questions max
        return array_slice($questions, 0, 6);
    }
}
