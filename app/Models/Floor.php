<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Machine;

class Floor extends Model
{
    protected $fillable = [
        'building_id',
        'name',
        'floor_number',
        'description',
        'status',
        'is_active',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function machines()
    {
        return $this->hasMany(Machine::class);
    }
}
