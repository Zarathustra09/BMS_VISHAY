<?php

namespace App\Services;

use App\Interfaces\AIInterface;
use App\Models\Machine;
use App\Services\Concerns\BuildsMachineContext;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService implements AIInterface
{
    use BuildsMachineContext;

    protected string $apiKey;
    protected string $apiUrl = 'https://api.openai.com/v1/responses';
    protected string $model = 'gpt-4o';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
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
     * Send request to OpenAI API and return response
     */
    public function sendRequest(string $input): ?string
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->apiUrl, [
                'model' => $this->model,
                'input' => $input,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Parse the response according to the API structure
                // Response has 'output' array with messages
                if (isset($data['output']) && is_array($data['output'])) {
                    foreach ($data['output'] as $output) {
                        if (isset($output['content']) && is_array($output['content'])) {
                            foreach ($output['content'] as $content) {
                                if (isset($content['type']) && $content['type'] === 'output_text') {
                                    return $content['text'];
                                }
                            }
                        }
                    }
                }

                Log::warning('OpenAI: Unexpected response structure', ['response' => $data]);
                return null;
            }

            Log::error('OpenAI API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('OpenAI request failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
