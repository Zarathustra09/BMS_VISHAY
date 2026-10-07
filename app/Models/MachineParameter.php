<?php

namespace App\Models;

use App\Enums\ParameterDataType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One reading a specific machine reports through the sensor API — e.g. "Coolant temperature".
 * Separate from the machine type's specification fields: those describe the machine, these are
 * the live values devices POST in a log's `readings`, keyed by `key`. A number parameter also
 * carries its alert bounds, which AlertService::evaluate() checks every incoming reading against.
 * `key` is generated once and never changes — see MachineParameterService::sync().
 */
class MachineParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'key',
        'label',
        'unit',
        'data_type',
        'required',
        'min',
        'max',
        'warning_min',
        'warning_max',
        'critical_min',
        'critical_max',
        'sort_order',
    ];

    protected $casts = [
        'required' => 'boolean',
        'data_type' => ParameterDataType::class,
        'min' => 'decimal:4',
        'max' => 'decimal:4',
        'warning_min' => 'decimal:4',
        'warning_max' => 'decimal:4',
        'critical_min' => 'decimal:4',
        'critical_max' => 'decimal:4',
        'sort_order' => 'integer',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function hasThresholds(): bool
    {
        return $this->warning_min !== null
            || $this->warning_max !== null
            || $this->critical_min !== null
            || $this->critical_max !== null;
    }

    /**
     * "Temperature (°C)" — the label with its unit, as shown in the chart dropdown and builder.
     */
    public function labelWithUnit(): string
    {
        return $this->unit ? "{$this->label} ({$this->unit})" : $this->label;
    }
}
