<?php

namespace App\Interfaces;

interface SettingInterface
{
    /**
     * The only keys SettingService::update() will persist — anything else in the submitted
     * array is silently ignored, so a stray form field can never create an untracked key.
     */
    public const KEYS = [
        'company_name',
        'alert_emails',
        'offline_timeout_minutes',
        'preventive_lead_days',
        'auto_fault_on_critical',
        'default_thresholds',
        'log_retention_days',
    ];

    /**
     * Used for any key with no row yet (fresh install, or a key added after some settings
     * were already saved).
     */
    public const DEFAULTS = [
        'company_name' => 'NexBuild BMS',
        'alert_emails' => [],
        'offline_timeout_minutes' => 10,
        'preventive_lead_days' => 7,
        'auto_fault_on_critical' => false,
        'default_thresholds' => [],
        'log_retention_days' => 365,
    ];

    /**
     * Every setting, decoded and merged over DEFAULTS. Cached — see SettingService.
     */
    public function all(): array;

    public function get(string $key, mixed $default = null): mixed;

    /**
     * Persists every key in $values that's in self::KEYS (anything else is ignored) and
     * invalidates the cache so the next read reflects it immediately.
     */
    public function update(array $values): void;
}
