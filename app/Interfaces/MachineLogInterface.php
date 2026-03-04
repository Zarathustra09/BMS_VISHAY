<?php

namespace App\Interfaces;

interface MachineLogInterface
{
    public function store(array $data): \App\Models\MachineLog;
}
