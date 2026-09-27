<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * A threshold breach (or, from Phase 6, a device-offline event) raised against a machine.
 * Created/escalated/auto-resolved by AlertService::evaluate() as sensor readings come in.
 */
class Alert extends Model
{
    use LogsActivity;

    /**
     * Deliberately narrower than logOnly($this->fillable) — AlertService::evaluate()
     * updates `value`/`machine_log_id` on every single reading while an alert stays open
     * (to track the latest reading behind it), which would otherwise flood the activity log
     * with one row per sensor reading. Only status/severity transitions — the events an
     * operator actually cares about — are logged.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'severity'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

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
