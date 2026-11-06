<?php

namespace App\Models;

use App\Interfaces\ReadingRollupInterface;
use App\Interfaces\SettingInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MachineLog model for storing per-machine sensor readings, events and maintenance logs.
 * Sensor values live in the `readings` JSON column, keyed by the machine's own MachineParameter keys,
 * so each machine reports whatever it actually measures with no schema change per new parameter.
 */
class MachineLog extends Model
{
    use HasFactory;
    use MassPrunable;

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
        'reading_stats',
        'notes',
        'performed_by',
        'logged_at',
    ];

    protected $casts = [
        'readings' => 'array',
        'reading_stats' => 'array',
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
     * Deleted by the daily `php artisan model:prune` scheduled entry (routes/console.php), in
     * chunked mass deletes (MassPrunable): a fast device writes up to 86,400 logs a day.
     *
     *  - any log older than Settings' log_retention_days, as before; and
     *  - a sensor reading older than Settings' reading_retention_days, but only once it has been
     *    summarised into per-minute rows (id at or below the rollup cursor), so the long charts
     *    keep that period and a stopped scheduler never loses data.
     *
     * Deleting a log that an Alert references only nulls that alert's machine_log_id
     * (nullOnDelete in the migration) — the alert itself is untouched.
     */
    public function prunable(): \Illuminate\Database\Eloquent\Builder
    {
        $settings = app(SettingInterface::class);

        $days = (int) $settings->get('log_retention_days', 365);
        $readingDays = (int) $settings->get('reading_retention_days', 7);
        $summarisedUpTo = app(ReadingRollupInterface::class)->cursor();

        return static::where(function ($query) use ($days, $readingDays, $summarisedUpTo) {
            $query->where('logged_at', '<', now()->subDays($days))
                ->orWhere(function ($query) use ($readingDays, $summarisedUpTo) {
                    $query->where('log_type', 'sensor_reading')
                        ->where('id', '<=', $summarisedUpTo)
                        ->where('logged_at', '<', now()->subDays($readingDays));
                });
        });
    }
}
