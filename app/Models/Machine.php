<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Machine model used by the Building Management System (manufacturing context).
 * Its type-specific specifications are stored as a `specifications` JSON column, keyed by the
 * field keys defined on its `machineType` — see App\Models\MachineType and
 * MachineService::normalizeSpecifications(). `power_rating` and `spec_voltage` stay as real
 * columns, since every machine has them regardless of type.
 */
class Machine extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    /**
     * Only fires when a fillable attribute actually changed (logOnlyDirty), skips the
     * device-key columns entirely (they're already hidden/encrypted — no reason to also
     * write their hashes into an audit log), and drops an update log with no real changes
     * (dontSubmitEmptyLogs) rather than recording a no-op.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'name',
        'machine_code',
        'machine_type_id',
        'manufacturer',
        'model_number',
        'serial_number',
        'building_id',
        'floor_id',
        'description',
        'specifications',
        'status',
        'power_rating',
        'ip_address',
        'installation_date',
        'last_maintenance_date',
        'next_maintenance_date',
        'maintenance_interval_days',
        'spec_voltage',
        'is_critical',
    ];

    /**
     * Never mass-assignable, and excluded from array/JSON output.
     * Viewing the plaintext key requires the `machines.view-device-key` permission — see decryptDeviceKey().
     */
    protected $hidden = [
        'device_key',
        'device_key_encrypted',
    ];

    protected $casts = [
        'is_critical' => 'boolean',
        'power_rating' => 'decimal:2',
        'installation_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'spec_voltage' => 'decimal:2',
        'specifications' => 'array',
        'building_id' => 'integer',
        'floor_id' => 'integer',
        'machine_type_id' => 'integer',
        'last_seen_at' => 'datetime',
    ];

    /**
     * Get all logs for this machine
     */
    public function logs(): HasMany
    {
        return $this->hasMany(MachineLog::class);
    }

    /**
     * The readings this machine reports through the sensor API (and their alert bounds),
     * in display order — see MachineParameter.
     */
    public function parameters(): HasMany
    {
        return $this->hasMany(MachineParameter::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Alerts raised against this machine (threshold breaches, and offline alerts).
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * Maintenance/repair tasks raised against this machine.
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    /**
     * Has this machine reported within the last $withinMinutes? False if it never has.
     */
    public function isOnline(int $withinMinutes): bool
    {
        return $this->last_seen_at !== null
            && $this->last_seen_at->greaterThan(now()->subMinutes($withinMinutes));
    }

    /**
     * Get recent logs
     */
    public function recentLogs(int $limit = 10): HasMany
    {
        return $this->logs()->orderBy('logged_at', 'desc')->limit($limit);
    }

    /**
     * Get the latest log entry
     */
    public function latestLog()
    {
        return $this->logs()->latest('logged_at')->first();
    }

    /**
     * Check if maintenance is due
     */
    public function isMaintenanceDue(): bool
    {
        return $this->next_maintenance_date &&
               $this->next_maintenance_date->isPast();
    }

    /**
     * Belongs to Building
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * Belongs to Floor
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    /**
     * The machine's type, whose spec_fields define what `specifications` holds for it.
     */
    public function machineType(): BelongsTo
    {
        return $this->belongsTo(MachineType::class);
    }

    /**
     * Scope for active machines
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for critical machines
     */
    public function scopeCritical($query)
    {
        return $query->where('is_critical', true);
    }

    /**
     * Scope by building id
     */
    public function scopeByBuilding($query, int $buildingId)
    {
        return $query->where('building_id', $buildingId);
    }

    /**
     * Scope by floor id
     */
    public function scopeByFloor($query, int $floorId)
    {
        return $query->where('floor_id', $floorId);
    }

    /**
     * Generate a new device API key for this machine.
     * Stores a SHA-256 hash (used for fast auth lookups) plus an encrypted copy
     * (so it can be viewed again later by a user with the `machines.view-device-key` permission).
     * A specific $plainKey can be supplied (the Docker demo install seeds a known one); otherwise
     * a random one is generated.
     */
    public function generateDeviceKey(?string $plainKey = null): string
    {
        $plainKey ??= bin2hex(random_bytes(20)); // 40 hex chars

        $this->forceFill([
            'device_key' => hash('sha256', $plainKey),
            'device_key_encrypted' => Crypt::encryptString($plainKey),
        ])->save();

        return $plainKey;
    }

    /**
     * Decrypt this machine's stored device key for display. Caller is responsible
     * for checking the `machines.view-device-key` permission before calling this.
     */
    public function decryptDeviceKey(): ?string
    {
        if (! $this->device_key_encrypted) {
            return null;
        }

        try {
            return Crypt::decryptString($this->device_key_encrypted);
        } catch (DecryptException) {
            return null;
        }
    }

    /**
     * Look up a machine by a plaintext device key sent by hardware.
     */
    public static function findByDeviceKey(string $plainKey): ?self
    {
        return static::where('device_key', hash('sha256', $plainKey))->first();
    }
}
