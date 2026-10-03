<?php

namespace App\Enums;

/**
 * The modules a deployment can switch off (see config/modules.php and docs/09-setup-and-operations.md,
 * "Editions & module toggles"). Machines, Machine Types, Alerts, Users/Roles/Permissions,
 * Settings, Profile and the sensor API are the always-on core and deliberately aren't listed.
 *
 * Each case owns the permission prefixes it seeds, so a disabled module's permissions can be
 * denied at the Gate (AppServiceProvider) and hidden from the role matrix without every call
 * site needing to know which module a permission belongs to.
 */
enum AppModule: string
{
    case Buildings = 'buildings';
    case WorkOrders = 'work-orders';
    case Maintenance = 'maintenance';
    case Reports = 'reports';

    public function isEnabled(): bool
    {
        return (bool) config("modules.{$this->value}", true);
    }

    /**
     * @return list<string>
     */
    public function permissionPrefixes(): array
    {
        return match ($this) {
            self::Buildings => ['buildings', 'floors'],
            self::WorkOrders => ['work-orders'],
            self::Maintenance => ['maintenance'],
            self::Reports => ['reports'],
        };
    }

    /**
     * @return list<string>
     */
    public static function disabledPermissionPrefixes(): array
    {
        $prefixes = [];

        foreach (self::cases() as $module) {
            if (! $module->isEnabled()) {
                array_push($prefixes, ...$module->permissionPrefixes());
            }
        }

        return $prefixes;
    }

    /**
     * Whether $ability ("work-orders.create", "buildings.view", …) belongs to a module that's
     * currently switched off. Anything that isn't a "{prefix}.{action}" name (a policy ability,
     * say) can't belong to a module, so it's never reported disabled.
     */
    public static function isAbilityDisabled(string $ability): bool
    {
        return in_array(explode('.', $ability, 2)[0], self::disabledPermissionPrefixes(), true);
    }
}
