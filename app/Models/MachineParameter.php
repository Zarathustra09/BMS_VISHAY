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
     * The saved bound as a clean number string ("60", not "60.0000"), or null when it isn't set.
     */
    public function bound(string $column): ?string
    {
        $value = $this->{$column};

        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        return str_contains($value, '.') ? (rtrim(rtrim($value, '0'), '.') ?: '0') : $value;
    }

    /**
     * Which alert level a reading of $value falls in: 'critical', 'warning', or null when it is
     * within bounds (or no bound applies). The ONE definition of the rule - AlertService raises
     * alerts with it, and the machine page shows each parameter's state with it. Critical bounds
     * are checked first, so critical limits that sit inside the warning limits swallow the warning
     * band entirely (see thresholdProblems()).
     */
    public function severityFor(float $value): ?string
    {
        $below = fn (string $column) => $this->{$column} !== null && $value < (float) $this->{$column};
        $above = fn (string $column) => $this->{$column} !== null && $value > (float) $this->{$column};

        if ($below('critical_min') || $above('critical_max')) {
            return 'critical';
        }

        if ($below('warning_min') || $above('warning_max')) {
            return 'warning';
        }

        return null;
    }

    /**
     * The alert limits in plain words, one line per level - e.g.
     * ['warning' => 'Warning below 60 or above 80 °C', 'critical' => 'Critical below 40 or above 100 °C'].
     * A level with no bounds is left out.
     *
     * @return array<string, string>
     */
    public function limitLines(): array
    {
        $unit = $this->unit ? " {$this->unit}" : '';
        $lines = [];

        foreach (['warning' => 'Warning', 'critical' => 'Critical'] as $level => $label) {
            $low = $this->bound("{$level}_min");
            $high = $this->bound("{$level}_max");
            $parts = array_filter([
                $low !== null ? "below {$low}" : null,
                $high !== null ? "above {$high}" : null,
            ]);

            if ($parts !== []) {
                $lines[$level] = "{$label} ".implode(' or ', $parts).$unit;
            }
        }

        return $lines;
    }

    /**
     * Ways the saved limits contradict each other, as sentences saying what they cause. Empty when
     * the limits make sense. The classic mistake: entering "warning 60-80, critical 81-100" as if
     * they were ranges - but "critical below 81" then makes every normal reading critical.
     *
     * @return array<int, string>
     */
    public function thresholdProblems(): array
    {
        $b = fn (string $column) => $this->{$column} !== null ? (float) $this->{$column} : null;
        [$wMin, $wMax, $cMin, $cMax] = [$b('warning_min'), $b('warning_max'), $b('critical_min'), $b('critical_max')];
        $problems = [];

        if ($wMin !== null && $wMax !== null && $wMin > $wMax) {
            $problems[] = "Warning below ({$this->bound('warning_min')}) is higher than Warning above ({$this->bound('warning_max')}), so every reading is a warning.";
        }

        if ($cMin !== null && $cMax !== null && $cMin > $cMax) {
            $problems[] = "Critical below ({$this->bound('critical_min')}) is higher than Critical above ({$this->bound('critical_max')}), so every reading is critical.";
        }

        if ($cMin !== null && $wMin !== null && $cMin > $wMin) {
            $problems[] = "Critical below ({$this->bound('critical_min')}) is higher than Warning below ({$this->bound('warning_min')}), so every reading under {$this->bound('critical_min')} is critical and the warning level is never reached. Critical below should be the lower number.";
        }

        if ($cMax !== null && $wMax !== null && $cMax < $wMax) {
            $problems[] = "Critical above ({$this->bound('critical_max')}) is lower than Warning above ({$this->bound('warning_max')}), so every reading over {$this->bound('critical_max')} is critical and the warning level is never reached. Critical above should be the higher number.";
        }

        return $problems;
    }

    /**
     * "Any" or "0 – 150" - the range the API accepts for this parameter.
     */
    public function rangeText(): string
    {
        $min = $this->bound('min');
        $max = $this->bound('max');

        return match (true) {
            $min !== null && $max !== null => "{$min} – {$max}",
            $min !== null => "≥ {$min}",
            $max !== null => "≤ {$max}",
            default => 'Any',
        };
    }

    /**
     * "Temperature (°C)" — the label with its unit, as shown in the chart dropdown and builder.
     */
    public function labelWithUnit(): string
    {
        return $this->unit ? "{$this->label} ({$this->unit})" : $this->label;
    }
}
