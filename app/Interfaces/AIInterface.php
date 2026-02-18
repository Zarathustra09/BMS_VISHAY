<?php

namespace App\Interfaces;

use App\Models\Machine;

interface AIInterface
{
    /**
     * Generate insights based on machine data and user message
     */
    public function generateInsights(Machine $machine, string $userMessage): string;

    /**
     * Build context/metadata from machine logs for the AI prompt
     */
    public function buildMachineContext(Machine $machine): string;

    /**
     * Send request to AI API and return response
     */
    public function sendRequest(string $input): ?string;
}
