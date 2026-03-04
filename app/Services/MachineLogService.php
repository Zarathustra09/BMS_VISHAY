<?php

namespace App\Services;

use App\Interfaces\MachineLogInterface;
use App\Models\MachineLog;

class MachineLogService implements MachineLogInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function store(array $data): MachineLog
    {
        if (empty($data['logged_at'])) {
            $data['logged_at'] = now();
        }

        return MachineLog::create($data);
    }
}
