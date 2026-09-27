<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * A machine type, replacing the hard-coded App\Enums\MachineType — each type owns its own
 * `spec_fields` definition (an ordered list of {key, label, type, unit?, step?, min?,
 * required} objects), so different types can carry different specifications without a
 * schema change. See docs/03-data-model.md for the exact `spec_fields` shape.
 */
class MachineType extends Model
{
    use HasFactory;
    use LogsActivity;

    /**
     * Only fires when a fillable attribute actually changed (logOnlyDirty), and drops an
     * update log with no real changes (dontSubmitEmptyLogs) — matches Machine's config.
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
        'icon',
        'description',
        'spec_fields',
    ];

    protected $casts = [
        'spec_fields' => 'array',
    ];

    /**
     * Machines currently assigned this type.
     */
    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class);
    }

    /**
     * This type's specification field definitions, or an empty list if none are set.
     *
     * @return array<int, array{key: string, label: string, type: string, unit?: ?string, step?: ?string, min?: ?float, required?: bool}>
     */
    public function specFields(): array
    {
        return $this->spec_fields ?? [];
    }
}
