<?php

namespace App\Enums;

/**
 * `machines.fuel` is a free varchar — this enum only backs the <datalist> of suggestions on
 * the machine form (see machines/_form.blade.php), it is not a validation constraint, since a
 * generator/HVAC/boiler in the field may genuinely run on something outside this list.
 */
enum FuelType: string
{
    case Diesel = 'Diesel';
    case Gasoline = 'Gasoline';
    case NaturalGas = 'Natural Gas';
    case Lpg = 'LPG';

    public function label(): string
    {
        return $this->value;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
