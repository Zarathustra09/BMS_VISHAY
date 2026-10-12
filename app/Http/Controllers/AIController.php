<?php

namespace App\Http\Controllers;

use App\Interfaces\SuggestedQuestionInterface;
use App\Jobs\ProcessMachineAIInsight;
use App\Models\Machine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AIController extends Controller
{
    public function __construct(protected SuggestedQuestionInterface $suggestedQuestions)
    {
        // The chat only exists on a machine's page, so it needs the same permission as that page.
        $this->middleware('permission:machines.view');
    }

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
        $cacheKey = 'ai_insight_'.Str::uuid()->toString();

        // Check if we have a recent cached response for similar queries
        $cachedResponseKey = 'ai_cache_'.md5($machineId.'_'.$userMessage);
        $cachedResponse = Cache::get($cachedResponseKey);

        if ($cachedResponse) {
            return response()->json([
                'success' => true,
                'cached' => true,
                'message' => $cachedResponse,
            ]);
        }

        // Who asked, so checkStatus() only ever hands an answer back to the user it was made for
        // (the queued job overwrites $cacheKey itself, so ownership lives under its own key).
        Cache::put($cacheKey.'_owner', $request->user()->id, now()->addHours(1));

        // Initialize cache entry as processing
        Cache::put($cacheKey, [
            'ready' => false,
            'message' => null,
        ], now()->addHours(1));

        // Dispatch the job to the queue
        ProcessMachineAIInsight::dispatch($machineId, $userMessage, $cacheKey);

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
            'cache_key' => ['required', 'string', 'regex:/^ai_insight_[0-9a-f-]{36}$/'],
        ]);

        $cacheKey = $request->input('cache_key');

        if (Cache::get($cacheKey.'_owner') !== $request->user()->id) {
            abort(404);
        }

        $cached = Cache::get($cacheKey);

        if (! $cached) {
            return response()->json([
                'ready' => false,
                'message' => 'Request not found or expired.',
            ]);
        }

        if ($cached['ready']) {
            // Cache the response for similar future queries if not an error
            if (! isset($cached['error'])) {
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
        return response()->json([
            'success' => true,
            'questions' => $this->suggestedQuestions->forMachine($machine),
        ]);
    }
}
