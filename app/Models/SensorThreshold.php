<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-machine, per-metric warning/critical bounds. AlertService::evaluate() checks every
 * incoming MachineLog reading against these to decide whether to raise or resolve an Alert.
 */
class SensorThreshold extends Model
{
    protected $fillable = [
        'machine_id',
        'metric',
        'warning_min',
        'warning_max',
        'critical_min',
        'critical_max',
    ];

    protected $casts = [
        'warning_min' => 'decimal:3',
        'warning_max' => 'decimal:3',
        'critical_min' => 'decimal:3',
        'critical_max' => 'decimal:3',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}
