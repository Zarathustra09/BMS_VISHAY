<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Machine;

class Building extends Model
{
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

    public function floors()
    {
        return $this->hasMany(Floor::class);
    }

    public function machines()
    {
        return $this->hasMany(Machine::class);
    }
}
