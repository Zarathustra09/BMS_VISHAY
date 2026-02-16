<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateAIInsightJob;
use App\Models\Machine;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AIController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Get predefined insight questions
     */
    public function getInsightQuestions(Machine $machine)
    {
        Log::info('Fetching AI insight questions', [
            'machine_id' => $machine->id,
            'machine_code' => $machine->machine_code,
        ]);

        $questions = [
            [
                'id' => 'health',
                'question' => 'What is the overall health status of this machine?',
                'icon' => '❤️',
                'category' => 'Health Analysis'
            ],
            [
                'id' => 'anomalies',
                'question' => 'Are there any anomalies or unusual patterns in recent logs?',
                'icon' => '🔍',
                'category' => 'Anomaly Detection'
            ],
            [
                'id' => 'performance',
                'question' => 'How has the machine performance trended over the last week?',
                'icon' => '📈',
                'category' => 'Performance'
            ],
            [
                'id' => 'maintenance',
                'question' => 'Should I schedule maintenance soon? What are the indicators?',
                'icon' => '🔧',
                'category' => 'Maintenance'
            ],
            [
                'id' => 'critical',
                'question' => 'What are the most critical issues that need immediate attention?',
                'icon' => '⚠️',
                'category' => 'Critical Issues'
            ],
            [
                'id' => 'power',
                'question' => 'How does current power consumption compare to normal levels?',
                'icon' => '⚡',
                'category' => 'Power Analysis'
            ],
            [
                'id' => 'recommendations',
                'question' => 'What are your top 3 recommendations for this machine?',
                'icon' => '💡',
                'category' => 'Recommendations'
            ],
            [
                'id' => 'failures',
                'question' => 'What are potential failure points based on the log data?',
                'icon' => '🚨',
                'category' => 'Risk Assessment'
            ],
        ];

        return response()->json([
            'success' => true,
            'questions' => $questions,
            'machine' => [
                'id' => $machine->id,
                'name' => $machine->name,
                'code' => $machine->machine_code
            ]
        ]);
    }

    /**
     * Send chat message and get AI response
     */
    public function chat(Request $request)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'message' => 'required|string|max:1000',
        ]);

        $machineId = $request->machine_id;
        $message = $request->message;

        // Create unique cache key
        $cacheKey = 'ai_insight_' . $machineId . '_' . md5($message);

        Log::info('AI Chat Request Received', [
            'machine_id' => $machineId,
            'message' => $message,
            'message_length' => strlen($message),
            'cache_key' => $cacheKey,
            'user_ip' => $request->ip(),
        ]);

        // Check if result already exists in cache
        if (Cache::has($cacheKey)) {
            $result = Cache::get($cacheKey);

            Log::info('AI Chat Response From Cache', [
                'machine_id' => $machineId,
                'cache_key' => $cacheKey,
                'success' => $result['success'] ?? false,
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['text'] ?? 'Processing...',
                'cached' => true,
                'processing' => false
            ]);
        }

        // Check if currently processing
        if (Cache::has($cacheKey . '_status')) {
            Log::debug('AI Chat Already Processing', [
                'machine_id' => $machineId,
                'cache_key' => $cacheKey,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Your request is being processed by our AI. Please wait...',
                'processing' => true,
                'cache_key' => $cacheKey
            ]);
        }

        // Dispatch job to queue
        Log::info('Dispatching AI Insight Job', [
            'machine_id' => $machineId,
            'message' => $message,
            'cache_key' => $cacheKey,
        ]);

        GenerateAIInsightJob::dispatch($machineId, $message, $cacheKey);

        return response()->json([
            'success' => true,
            'message' => 'Processing your request with AI. This may take a few moments...',
            'processing' => true,
            'cache_key' => $cacheKey
        ]);
    }

    /**
     * Check status of AI processing
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'cache_key' => 'required|string',
        ]);

        $cacheKey = $request->cache_key;

        Log::debug('Checking AI processing status', [
            'cache_key' => $cacheKey,
        ]);

        // Check if still processing
        if (Cache::has($cacheKey . '_status')) {
            return response()->json([
                'success' => true,
                'ready' => false,
                'processing' => true,
                'message' => 'Still analyzing machine data...'
            ]);
        }

        // Check if result is ready
        if (Cache::has($cacheKey)) {
            $result = Cache::get($cacheKey);

            Log::info('AI processing completed', [
                'cache_key' => $cacheKey,
                'success' => $result['success'] ?? false,
            ]);

            return response()->json([
                'success' => true,
                'ready' => true,
                'processing' => false,
                'message' => $result['text'] ?? 'Response ready',
                'data' => $result
            ]);
        }

        Log::warning('AI result not found', [
            'cache_key' => $cacheKey,
        ]);

        return response()->json([
            'success' => false,
            'ready' => false,
            'processing' => false,
            'message' => 'No result found. Please try again.'
        ]);
    }

    /**
     * Quick insight - process immediately without queue
     */
    public function quickInsight(Request $request, Machine $machine)
    {
        $request->validate([
            'question' => 'required|string|max:500',
        ]);

        try {
            $machine->load(['logs' => function($query) {
                $query->orderBy('logged_at', 'desc')->limit(20);
            }]);

            $machineData = [
                'name' => $machine->name,
                'machine_code' => $machine->machine_code,
                'type' => $machine->type,
                'status' => $machine->status,
                'location' => $machine->location,
                'power_rating' => $machine->power_rating,
            ];

            $logsData = [
                'total' => $machine->logs()->count(),
                'critical' => $machine->logs()->where('severity', 'critical')->count(),
                'errors' => $machine->logs()->where('severity', 'error')->count(),
                'warnings' => $machine->logs()->where('severity', 'warning')->count(),
                'today' => $machine->logs()->whereDate('logged_at', today())->count(),
                'week' => $machine->logs()->where('logged_at', '>=', now()->subWeek())->count(),
                'recent_logs' => $machine->logs->take(10)->map(function($log) {
                    return [
                        'severity' => $log->severity,
                        'event' => $log->event,
                        'logged_at' => $log->logged_at->format('Y-m-d H:i:s'),
                        'temperature' => $log->temperature,
                        'power_consumption' => $log->power_consumption,
                    ];
                })->toArray(),
            ];

            $prompt = $this->geminiService->buildAnalysisPrompt(
                $machineData,
                $logsData,
                $request->question
            );

            $result = $this->geminiService->generateContent($prompt);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['text'],
                'cached' => false
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate insight: ' . $e->getMessage()
            ], 500);
        }
    }
}
