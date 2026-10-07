<?php

namespace App\Enums;

use App\Models\MachineParameter;

/**
 * The kinds of reading a machine parameter can carry — what a device is allowed to send for
 * it in a POST body's `readings` object. Decides how a submitted value is validated
 * (valueRules()), how it is cast for storage (cast()), and whether alert thresholds apply
 * (supportsThresholds()).
 */
enum ParameterDataType: string
{
    case Number = 'number';
    case Boolean = 'boolean';
    case Text = 'text';

    public function label(): string
    {
        return match ($this) {
            self::Number => 'Number',
            self::Boolean => 'Yes / No',
            self::Text => 'Text',
        };
    }

    /**
     * Only a number has an ordering, so only a number can breach a min/max bound or be charted.
     */
    public function supportsThresholds(): bool
    {
        return $this === self::Number;
    }

    /**
     * The `readings.{key}` validation rules for a device submitting a value for $parameter.
     * A strict boolean is required (true/false/0/1) — a stray string like "yes" is rejected
     * rather than silently coerced.
     *
     * @return list<mixed>
     */
    public function valueRules(MachineParameter $parameter): array
    {
        return match ($this) {
            self::Number => array_values(array_filter([
                'numeric',
                $parameter->min !== null ? 'min:'.(float) $parameter->min : null,
                $parameter->max !== null ? 'max:'.(float) $parameter->max : null,
            ])),
            self::Boolean => ['boolean'],
            self::Text => ['string', 'max:255'],
        };
    }

    public function cast(mixed $value): mixed
    {
        return match ($this) {
            self::Number => (float) $value,
            self::Boolean => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            self::Text => (string) $value,
        };
    }

    /**
     * A representative value for the machine's API Integration example body.
     */
    public function example(MachineParameter $parameter): mixed
    {
        return match ($this) {
            self::Number => $this->exampleNumber($parameter),
            self::Boolean => false,
            self::Text => 'OK',
        };
    }

    /**
     * @return array<string, string> value => label, for the builder's data type <select>
     */
    public static function options(): array
    {
        return array_combine(
            array_map(fn (self $case) => $case->value, self::cases()),
            array_map(fn (self $case) => $case->label(), self::cases()),
        );
    }

    private function exampleNumber(MachineParameter $parameter): float|int
    {
        $min = $parameter->min !== null ? (float) $parameter->min : null;
        $max = $parameter->max !== null ? (float) $parameter->max : null;

        if ($min !== null && $max !== null) {
            return round(($min + $max) / 2, 1);
        }

        return $min ?? ($max !== null ? min(0, $max) : 42.5);
    }
}
