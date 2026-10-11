<?php

namespace App\Services;

use App\Interfaces\SettingInterface;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Applies the outgoing-email details saved on the Settings page on top of the MAIL_* environment
 * config, so an admin can set up SMTP from the browser instead of editing server files.
 *
 * No host saved = nothing changes and mail keeps using the environment mailer (the `log` mailer
 * in a fresh install, which only writes emails to the log).
 *
 * apply() runs on every boot AND before every queued job (AppServiceProvider): the queue worker is
 * a long-running process that booted long before the settings were saved, and alert emails are
 * sent from there.
 */
class MailSettings
{
    public function __construct(protected SettingInterface $settings) {}

    public function apply(): void
    {
        $settings = $this->settings->all();

        if (filled($settings['mail_host'] ?? null)) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $settings['mail_host'],
                'mail.mailers.smtp.port' => (int) ($settings['mail_port'] ?: 587),
                'mail.mailers.smtp.username' => filled($settings['mail_username'] ?? null) ? $settings['mail_username'] : null,
                'mail.mailers.smtp.password' => $this->decryptedPassword($settings['mail_password'] ?? ''),
                // STARTTLS (port 587) is negotiated by the "smtp" scheme; implicit TLS (465) is "smtps".
                'mail.mailers.smtp.scheme' => ($settings['mail_encryption'] ?? 'tls') === 'ssl' ? 'smtps' : 'smtp',
            ]);

            if (filled($settings['mail_from_address'] ?? null)) {
                config(['mail.from.address' => $settings['mail_from_address']]);
            }

            if (filled($settings['mail_from_name'] ?? null)) {
                config(['mail.from.name' => $settings['mail_from_name']]);
            }

            // A cached mailer keeps the transport it was built with.
            Mail::purge('smtp');
            Mail::purge();
        }

        // Emails are built by the queue worker, which has no request to take the site address
        // from - so links in them (View Machine) would point at the env APP_URL. Web requests keep
        // using the address the browser used.
        if (app()->runningInConsole() && filled($settings['app_url'] ?? null)) {
            config(['app.url' => $settings['app_url']]);
            URL::forceRootUrl($settings['app_url']);
        }
    }

    private function decryptedPassword(string $stored): ?string
    {
        if ($stored === '') {
            return null;
        }

        try {
            return Crypt::decryptString($stored);
        } catch (DecryptException) {
            // APP_KEY changed since the password was saved - treat as unset rather than failing every request.
            return null;
        }
    }
}
