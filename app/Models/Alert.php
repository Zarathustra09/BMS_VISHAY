<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A threshold breach (or, from Phase 6, a device-offline event) raised against a machine.
 * Created/escalated/auto-resolved by AlertService::evaluate() as sensor readings come in.
 */
class Alert extends Model
{
    protected $fillable = [
        'machine_id',
        'machine_log_id',
        'type',
        'metric',
        'value',
        'severity',
        'status',
        'message',
        'acknowledged_by',
        'acknowledged_at',
        'resolved_at',
    ];

    protected $casts = [
        'value' => 'decimal:3',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function machineLog(): BelongsTo
    {
        return $this->belongsTo(MachineLog::class);
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
