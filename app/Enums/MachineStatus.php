<?php

namespace App\Enums;

/**
 * Mirrors the `machines.status` DB enum('active','inactive','maintenance','fault'). Not used
 * as a model cast (see Machine::casts()) — MachineService::getStats() and the DataTable
 * filters query the raw string column, and casting risks throwing on any value outside this
 * set. Validation (see StoreMachineRequest/UpdateMachineRequest) is the actual guard.
 */
enum MachineStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Maintenance = 'maintenance';
    case Fault = 'fault';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Maintenance => 'Maintenance',
            self::Fault => 'Fault',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Active => '🟢',
            self::Inactive => '⚪',
            self::Maintenance => '🟡',
            self::Fault => '🔴',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'badge-success',
            self::Inactive => 'badge-ghost',
            self::Maintenance => 'badge-warning',
            self::Fault => 'badge-error',
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
