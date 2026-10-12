<?php

namespace App\Models;

use App\Interfaces\SettingInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MachineLog model for storing per-machine sensor readings, events and maintenance logs.
 * Sensor values live in the `readings` JSON column, keyed by the machine's own MachineParameter keys,
 * so each machine reports whatever it actually measures with no schema change per new parameter.
 */
class MachineLog extends Model
{
    use HasFactory;
    use Prunable;

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
        'readings',
        'notes',
        'performed_by',
        'logged_at',
    ];

    protected $casts = [
        'readings' => 'array',
        'logged_at' => 'datetime',
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

    /**
     * Rows older than Settings' log_retention_days are deleted by the daily
     * `php artisan model:prune` scheduled entry (routes/console.php). Deleting a log that an
     * Alert references only nulls that alert's machine_log_id (nullOnDelete in the
     * migration) — the alert itself is untouched.
     */
    public function prunable(): \Illuminate\Database\Eloquent\Builder
    {
        $days = app(SettingInterface::class)->get('log_retention_days', 365);

        return static::where('logged_at', '<', now()->subDays($days));
    }
}
