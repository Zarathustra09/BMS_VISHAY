<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Machine model used by the Building Management System (manufacturing context).
 * Stores explicit specification columns (no JSON) for easy querying and reporting.
 */
class Machine extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'machine_code',
        'type',
        'manufacturer',
        'model_number',
        'serial_number',
        'building_id',
        'floor_id',
        'description',
        'status',
        'power_rating',
        'ip_address',
        'installation_date',
        'last_maintenance_date',
        'next_maintenance_date',
        'maintenance_interval_days',
        'capacity_tons',
        'spec_voltage',
        'fuel',
        'autostart',
        'capacity_persons',
        'speed_m_s',
        'flow_lpm',
        'head_m',
        'cells',
        'makeup_valve',
        'is_critical',
    ];

    protected $casts = [
        'is_critical' => 'boolean',
        'power_rating' => 'decimal:2',
        'installation_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'capacity_tons' => 'decimal:2',
        'spec_voltage' => 'decimal:2',
        'autostart' => 'boolean',
        'capacity_persons' => 'integer',
        'speed_m_s' => 'decimal:2',
        'flow_lpm' => 'integer',
        'head_m' => 'decimal:2',
        'cells' => 'integer',
        'makeup_valve' => 'boolean',
        'building_id' => 'integer',
        'floor_id' => 'integer',
    ];

    /**
     * Get all logs for this machine
     */
    public function logs(): HasMany
    {
        return $this->hasMany(MachineLog::class);
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
}
