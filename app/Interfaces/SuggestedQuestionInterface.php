<?php

namespace App\Interfaces;

use App\Models\Machine;

interface SuggestedQuestionInterface
{
    /**
     * Generate context-aware suggested AI questions based on a machine's current state
     * (recent alerts, maintenance schedule, available sensor data, status, log volume).
     */
    public function forMachine(Machine $machine): array;
}
