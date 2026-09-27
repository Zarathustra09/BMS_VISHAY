<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Machine;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Building extends Model
{
    use HasFactory;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'name',
        'code',
        'type',
        'image',
        'address',
        'city',
        'state',
        'emergency_contact_name',
        'emergency_contact_phone',
        'status',
        'notes',
    ];

    protected static function boot()
    {
        parent::boot();

        // Delete related floors and machines when building is deleted
        static::deleting(function ($building) {
            $building->floors()->delete();
            $building->machines()->delete();
        });
    }

    public function floors()
    {
        return $this->hasMany(Floor::class);
    }

    public function machines()
    {
        return $this->hasMany(Machine::class);
    }
}
