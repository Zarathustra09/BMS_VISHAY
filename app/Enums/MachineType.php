<?php

namespace App\Enums;

/**
 * The machine `type` column is a free varchar (see the migration comment), but the UI has
 * always offered exactly this fixed set — this enum centralises what used to be an identical
 * hard-coded option/icon list duplicated across machines/create, machines/edit,
 * machines/delete and machines/show.
 */
enum MachineType: string
{
    case Chiller = 'Chiller';
    case Generator = 'Generator';
    case Elevator = 'Elevator';
    case Pump = 'Pump';
    case CoolingTower = 'Cooling Tower';
    case Hvac = 'HVAC';
    case Compressor = 'Compressor';
    case Boiler = 'Boiler';
    case Other = 'Other';

    public function label(): string
    {
        return $this->value;
    }

    public function icon(): string
    {
        return match ($this) {
            self::Chiller => '🧊',
            self::Generator => '⚡',
            self::Elevator => '🛗',
            self::Pump => '💧',
            self::CoolingTower => '🏗️',
            self::Hvac => '🌡️',
            self::Compressor => '🔧',
            self::Boiler => '🔥',
            self::Other => '⚙️',
        };
    }

    /**
     * The type-specific spec fields shown on the machine form for this type — see
     * machines/_form.blade.php's `specFields` map, which mirrors this list so the same field
     * is never rendered twice for a given type (a bug in the old wizard: capacity_tons and
     * fuel were each rendered in two places at once, and both hidden copies posted).
     *
     * @return list<string>
     */
    public function specFields(): array
    {
        return match ($this) {
            self::Chiller => ['capacity_tons'],
            self::CoolingTower => ['capacity_tons', 'cells', 'makeup_valve'],
            self::Generator => ['fuel', 'autostart'],
            self::Elevator => ['capacity_persons', 'speed_m_s'],
            self::Pump => ['flow_lpm', 'head_m'],
            self::Hvac, self::Compressor, self::Boiler, self::Other => ['fuel'],
        };
    }

    /**
     * @return array<string, string> value => label, for populating a <select>.
     */
    public static function options(): array
    {
        return array_combine(
            array_map(fn (self $case) => $case->value, self::cases()),
            array_map(fn (self $case) => $case->icon().' '.$case->label(), self::cases()),
        );
    }
}
