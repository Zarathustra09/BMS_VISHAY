<?php

namespace App\Services;

use App\Interfaces\AIInterface;
use App\Models\Machine;
use App\Services\Concerns\BuildsMachineContext;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService implements AIInterface
{
    use BuildsMachineContext;

    protected $apiKey;
    protected $baseUrl;
    protected $model = 'gemini-2.0-flash-exp'; // Updated to Gemini 2.0 Flash

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->baseUrl = config('services.gemini.base_url');
    }

    /**
     * Generate insights based on machine data and user message
     */
    public function generateInsights(Machine $machine, string $userMessage): string
    {
        $context = $this->buildMachineContext($machine);

        $input = $this->buildPrompt($context, $userMessage);

        $response = $this->sendRequest($input);

        return $response ?? 'Unable to generate insights at this time. Please try again later.';
    }

    /**
     * Send request to Gemini API and return the generated text, or null on failure.
     */
    public function sendRequest(string $input): ?string
    {
        $result = $this->generateContent($input);

        return $result['success'] ? $result['text'] : null;
    }

    /**
     * Generate content using Gemini API
     */
    public function generateContent(string $prompt): array
    {
        $startTime = microtime(true);
        $requestId = uniqid('gemini_', true);

        // Log the request
        Log::info('Gemini API Request Started', [
            'request_id' => $requestId,
            'base_url' => $this->baseUrl,
            'model' => $this->model,
            'prompt_length' => strlen($prompt),
            'prompt_preview' => substr($prompt, 0, 200) . '...',
            'has_api_key' => !empty($this->apiKey),
            'api_key_prefix' => $this->apiKey ? substr($this->apiKey, 0, 8) . '...' : 'NOT_SET',
        ]);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $this->apiKey,
                ])
                ->post("{$this->baseUrl}/models/{$this->model}:generateContent", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 2048, // Increased for Gemini 2.0
                        'topP' => 0.95,
                        'topK' => 40,
                    ]
                ]);

            $duration = microtime(true) - $startTime;

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response generated.';

                // Log successful response
                Log::info('Gemini API Response Successful', [
                    'request_id' => $requestId,
                    'model' => $this->model,
                    'status_code' => $response->status(),
                    'duration_seconds' => round($duration, 2),
                    'response_length' => strlen($text),
                    'response_preview' => substr($text, 0, 200) . '...',
                    'candidates_count' => count($data['candidates'] ?? []),
                    'finish_reason' => $data['candidates'][0]['finishReason'] ?? 'unknown',
                ]);

                return [
                    'success' => true,
                    'text' => $text,
                    'raw' => $data
                ];
            }

            // Log API error response
            Log::error('Gemini API Error Response', [
                'request_id' => $requestId,
                'model' => $this->model,
                'status_code' => $response->status(),
                'duration_seconds' => round($duration, 2),
                'error_body' => $response->body(),
                'headers' => $response->headers(),
            ]);

            return [
                'success' => false,
                'text' => 'Failed to generate response. Please try again.',
                'error' => $response->body()
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $duration = microtime(true) - $startTime;

            // Log connection errors
            Log::error('Gemini API Connection Exception', [
                'request_id' => $requestId,
                'model' => $this->model,
                'duration_seconds' => round($duration, 2),
                'exception_type' => get_class($e),
                'message' => $e->getMessage(),
                'api_url' => "{$this->baseUrl}/models/{$this->model}:generateContent",
            ]);

            return [
                'success' => false,
                'text' => 'Connection error. Please check your network and try again.',
                'error' => $e->getMessage()
            ];

        } catch (\Illuminate\Http\Client\RequestException $e) {
            $duration = microtime(true) - $startTime;

            // Log request exceptions
            Log::error('Gemini API Request Exception', [
                'request_id' => $requestId,
                'model' => $this->model,
                'duration_seconds' => round($duration, 2),
                'exception_type' => get_class($e),
                'message' => $e->getMessage(),
                'response' => $e->response ? $e->response->body() : null,
            ]);

            return [
                'success' => false,
                'text' => 'Request failed. Please try again.',
                'error' => $e->getMessage()
            ];

        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;

            // Log general exceptions
            Log::error('Gemini API General Exception', [
                'request_id' => $requestId,
                'model' => $this->model,
                'duration_seconds' => round($duration, 2),
                'exception_type' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'text' => 'An error occurred while processing your request.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build prompt for machine log analysis
     */
    public function buildAnalysisPrompt(array $machineData, array $logsData, string $question): string
    {
        // Log prompt building
        Log::debug('Building Gemini Analysis Prompt', [
            'machine_code' => $machineData['machine_code'] ?? 'unknown',
            'machine_type' => $machineData['type'] ?? 'unknown',
            'total_logs' => $logsData['total'] ?? 0,
            'critical_logs' => $logsData['critical'] ?? 0,
            'question' => $question,
        ]);

        $machineInfo = "Machine Information:\n";
        $machineInfo .= "- Name: {$machineData['name']}\n";
        $machineInfo .= "- Code: {$machineData['machine_code']}\n";
        $machineInfo .= "- Type: {$machineData['type']}\n";
        $machineInfo .= "- Status: {$machineData['status']}\n";
        $machineInfo .= "- Location: {$machineData['location']}\n";

        if (!empty($machineData['power_rating'])) {
            $machineInfo .= "- Power Rating: {$machineData['power_rating']} kW\n";
        }

        $logsSummary = "\nLog Statistics:\n";
        $logsSummary .= "- Total Logs: " . ($logsData['total'] ?? 0) . "\n";
        $logsSummary .= "- Critical: " . ($logsData['critical'] ?? 0) . "\n";
        $logsSummary .= "- Errors: " . ($logsData['errors'] ?? 0) . "\n";
        $logsSummary .= "- Warnings: " . ($logsData['warnings'] ?? 0) . "\n";
        $logsSummary .= "- Today: " . ($logsData['today'] ?? 0) . "\n";
        $logsSummary .= "- This Week: " . ($logsData['week'] ?? 0) . "\n";

        $recentLogsInfo = "\nRecent Log Entries (Last 10):\n";
        if (!empty($logsData['recent_logs'])) {
            foreach (array_slice($logsData['recent_logs'], 0, 10) as $index => $log) {
                $recentLogsInfo .= ($index + 1) . ". [{$log['severity']}] {$log['event']}\n";
                $recentLogsInfo .= "   Time: {$log['logged_at']}\n";
                if (!empty($log['temperature'])) {
                    $recentLogsInfo .= "   Temperature: {$log['temperature']}°C\n";
                }
                if (!empty($log['power_consumption'])) {
                    $recentLogsInfo .= "   Power: {$log['power_consumption']} kW\n";
                }
                if (!empty($log['vibration_rms'])) {
                    $recentLogsInfo .= "   Vibration: {$log['vibration_rms']} RMS\n";
                }
                $recentLogsInfo .= "\n";
            }
        }

        $prompt = "You are an expert Building Management System (BMS) analyst specializing in manufacturing equipment maintenance and diagnostics.\n\n";
        $prompt .= $machineInfo;
        $prompt .= $logsSummary;
        $prompt .= $recentLogsInfo;
        $prompt .= "\nUser Question: {$question}\n\n";
        $prompt .= "Provide a professional, concise, and actionable answer based on the machine data and logs. ";
        $prompt .= "Use bullet points where appropriate. Be specific and technical but clear.";

        Log::debug('Gemini Prompt Built', [
            'prompt_length' => strlen($prompt),
            'machine_info_length' => strlen($machineInfo),
            'logs_summary_length' => strlen($logsSummary),
            'recent_logs_count' => count($logsData['recent_logs'] ?? []),
        ]);

        return $prompt;
    }
}
