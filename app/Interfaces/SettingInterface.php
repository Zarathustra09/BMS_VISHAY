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
        'log_retention_days',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'app_url',
    ];

    /**
     * Used for any key with no row yet (fresh install, or a key added after some settings
     * were already saved).
     */
    public const DEFAULTS = [
        'company_name' => 'LogXense Enterprise',
        'alert_emails' => [],
        'offline_timeout_minutes' => 10,
        'preventive_lead_days' => 7,
        'auto_fault_on_critical' => false,
        'log_retention_days' => 365,
        // Outgoing email (see MailSettings). Empty host = keep using the MAIL_* environment mailer.
        'mail_host' => '',
        'mail_port' => 587,
        'mail_username' => '',
        'mail_password' => '', // stored encrypted, never shown again
        'mail_encryption' => 'tls',
        'mail_from_address' => '',
        'mail_from_name' => '',
        'app_url' => '', // address the site is opened at, used for links inside emails
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
