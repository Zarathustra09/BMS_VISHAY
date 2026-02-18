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
            'category' => 'general',
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
                'category' => 'alerts',
            ];

            $questions[] = [
                'icon' => '🔴',
                'question' => 'What are the root causes of critical issues?',
                'category' => 'alerts',
            ];
        }

        // Check for errors
        $errorCount = $machine->logs()
            ->where('severity', 'error')
            ->where('logged_at', '>=', now()->subWeek())
            ->count();

        if ($errorCount > 0) {
            $questions[] = [
                'icon' => '⚠️',
                'question' => 'Summarize all errors from the past week',
                'category' => 'alerts',
            ];
        }

        // Check maintenance status
        if ($machine->next_maintenance_date) {
            $daysUntil = now()->diffInDays($machine->next_maintenance_date, false);
            if ($daysUntil < 0) {
                $questions[] = [
                    'icon' => '🔧',
                    'question' => 'What maintenance tasks are overdue and what are the risks?',
                    'category' => 'maintenance',
                ];

                $questions[] = [
                    'icon' => '⏰',
                    'question' => 'Create an urgent maintenance action plan',
                    'category' => 'maintenance',
                ];
            } elseif ($daysUntil <= 7) {
                $questions[] = [
                    'icon' => '📅',
                    'question' => 'What should be checked in the upcoming maintenance?',
                    'category' => 'maintenance',
                ];
            } elseif ($daysUntil <= 30) {
                $questions[] = [
                    'icon' => '🛠️',
                    'question' => 'Prepare a checklist for the next maintenance',
                    'category' => 'maintenance',
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
                    'category' => 'sensors',
                ];

                $questions[] = [
                    'icon' => '🔥',
                    'question' => 'Analyze temperature patterns over the last week',
                    'category' => 'sensors',
                ];
            }

            if ($recentLog->power_consumption !== null) {
                $questions[] = [
                    'icon' => '⚡',
                    'question' => 'Analyze power consumption patterns and efficiency',
                    'category' => 'performance',
                ];

                $questions[] = [
                    'icon' => '💰',
                    'question' => 'How can we reduce energy costs for this machine?',
                    'category' => 'performance',
                ];
            }

            if ($recentLog->vibration_rms !== null) {
                $questions[] = [
                    'icon' => '📊',
                    'question' => 'Are vibration levels within normal range?',
                    'category' => 'sensors',
                ];

                $questions[] = [
                    'icon' => '🔊',
                    'question' => 'Compare current vibration with historical data',
                    'category' => 'sensors',
                ];
            }

            if ($recentLog->pressure !== null) {
                $questions[] = [
                    'icon' => '💨',
                    'question' => 'Is the pressure operating within specifications?',
                    'category' => 'sensors',
                ];
            }

            if ($recentLog->voltage !== null || $recentLog->current !== null) {
                $questions[] = [
                    'icon' => '🔌',
                    'question' => 'Check electrical parameters for abnormalities',
                    'category' => 'sensors',
                ];
            }
        }

        // Performance optimization questions
        $questions[] = [
            'icon' => '📈',
            'question' => 'What optimizations can improve this machine\'s performance?',
            'category' => 'performance',
        ];

        $questions[] = [
            'icon' => '⏱️',
            'question' => 'Analyze runtime hours and utilization efficiency',
            'category' => 'performance',
        ];

        // Add risk assessment for critical machines
        if ($machine->is_critical) {
            $questions[] = [
                'icon' => '⚠️',
                'question' => 'What are the current risk factors for this critical system?',
                'category' => 'risk',
            ];

            $questions[] = [
                'icon' => '🛡️',
                'question' => 'Recommend preventive measures to avoid downtime',
                'category' => 'risk',
            ];
        }

        // Predictive questions based on historical data
        $totalLogs = $machine->logs()->count();
        if ($totalLogs > 100) {
            $questions[] = [
                'icon' => '🔮',
                'question' => 'Predict potential failures based on historical trends',
                'category' => 'predictive',
            ];

            $questions[] = [
                'icon' => '📉',
                'question' => 'What degradation patterns do you see over time?',
                'category' => 'predictive',
            ];
        }

        // Status-specific questions
        switch ($machine->status) {
            case 'maintenance':
                $questions[] = [
                    'icon' => '🔧',
                    'question' => 'When can this machine safely return to operation?',
                    'category' => 'status',
                ];
                break;
            case 'fault':
                $questions[] = [
                    'icon' => '🆘',
                    'question' => 'What immediate actions should be taken for this fault?',
                    'category' => 'status',
                ];
                break;
            case 'inactive':
                $questions[] = [
                    'icon' => '💤',
                    'question' => 'Why is this machine inactive and when will it restart?',
                    'category' => 'status',
                ];
                break;
        }

        // Comparative questions if there are enough logs
        if ($totalLogs > 50) {
            $questions[] = [
                'icon' => '📊',
                'question' => 'Compare this week\'s performance with last week',
                'category' => 'comparison',
            ];

            $questions[] = [
                'icon' => '📅',
                'question' => 'Show month-over-month performance trends',
                'category' => 'comparison',
            ];
        }

        // Compliance and reporting
        $questions[] = [
            'icon' => '📋',
            'question' => 'Generate a status report for management',
            'category' => 'reporting',
        ];

        $questions[] = [
            'icon' => '📝',
            'question' => 'List all anomalies detected in the last 30 days',
            'category' => 'reporting',
        ];

        // Troubleshooting
        $questions[] = [
            'icon' => '🔍',
            'question' => 'Help me troubleshoot unusual behavior',
            'category' => 'troubleshooting',
        ];

        // Best practices
        $questions[] = [
            'icon' => '💡',
            'question' => 'What are best practices for operating this machine type?',
            'category' => 'guidance',
        ];

        // Prioritize by category relevance
        $priorityCategories = ['alerts', 'maintenance', 'risk', 'status'];
        $priorityQuestions = array_filter($questions, fn($q) => in_array($q['category'] ?? '', $priorityCategories));
        $otherQuestions = array_filter($questions, fn($q) => !in_array($q['category'] ?? '', $priorityCategories));

        // Take up to 8 priority questions and up to 4 other questions
        $selectedQuestions = array_merge(
            array_slice($priorityQuestions, 0, 8),
            array_slice($otherQuestions, 0, 4)
        );

        // Remove category key before returning
        return array_values(array_map(function($q) {
            unset($q['category']);
            return $q;
        }, array_slice($selectedQuestions, 0, 12)));
    }
}
