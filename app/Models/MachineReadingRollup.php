<?php

namespace App\Models;

use App\Interfaces\SettingInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One minute of one number parameter on one machine: its lowest, highest, sum and sample count.
 * Written by ReadingRollupService from machine_logs; read by the sensor chart for long windows.
 */
class MachineReadingRollup extends Model
{
    use MassPrunable;

    public $timestamps = false;

    protected $fillable = [
        'machine_id',
        'metric',
        'period_start',
        'min',
        'max',
        'sum',
        'count',
    ];

    protected $casts = [
        'period_start' => 'integer',
        'min' => 'float',
        'max' => 'float',
        'sum' => 'float',
        'count' => 'integer',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Summaries live as long as the machine log retention setting, the same as events.
     */
    public function prunable(): Builder
    {
        $days = (int) app(SettingInterface::class)->get('log_retention_days', 365);

        return static::where('period_start', '<', now()->subDays($days)->getTimestamp());
    }
}
