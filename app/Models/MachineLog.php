<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MachineLog model for storing per-machine sensor readings, events and maintenance logs.
 * Uses distinct columns for sensor values (no JSON) to support analytics and alerts in manufacturing BMS.
 */
class MachineLog extends Model
{
    /**
     * Explicit table name for clarity (matches migration).
     *
     * @var string
     */
    protected $table = 'machine_logs';

    protected $fillable = [
        'machine_id',
        'log_type',
        'event',
        'severity',
        'previous_status',
        'current_status',
        'temperature',
        'humidity',
        'pressure',
        'power_consumption',
        'voltage',
        'current',
        'frequency',
        'runtime_hours',
        'vibration_rms',
        'bearing_temp',
        'oil_level',
        'notes',
        'performed_by',
        'logged_at',
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'humidity' => 'decimal:2',
        'pressure' => 'decimal:2',
        'power_consumption' => 'decimal:2',
        'voltage' => 'decimal:2',
        'current' => 'decimal:2',
        'frequency' => 'decimal:2',
        'logged_at' => 'datetime',
        'vibration_rms' => 'decimal:3',
        'bearing_temp' => 'decimal:2',
        'oil_level' => 'decimal:2',
    ];

    /**
     * Get the machine that owns the log
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Scope for errors and critical logs
     */
    public function scopeAlerts($query)
    {
        return $query->whereIn('severity', ['error', 'critical']);
    }

    /**
     * Scope by log type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('log_type', $type);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('logged_at', '>=', now()->subDays($days));
    }

    /**
     * Scope by severity
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }
}
