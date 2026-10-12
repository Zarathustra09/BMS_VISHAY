<?php

namespace App\Http\Controllers;

use App\Interfaces\SettingInterface;
use App\Services\MailSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class SettingController extends Controller
{
    public function __construct(protected SettingInterface $settingService)
    {
        $this->middleware('permission:settings.view')->only(['index']);
        $this->middleware('permission:settings.update')->only(['update', 'testEmail']);
    }

    public function index()
    {
        $settings = $this->settingService->all();

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'alert_emails' => 'nullable|string',
            'offline_timeout_minutes' => 'required|integer|min:1',
            'preventive_lead_days' => 'required|integer|min:0',
            'auto_fault_on_critical' => 'boolean',
            'log_retention_days' => 'required|integer|min:1',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|in:tls,ssl,none',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
            'app_url' => 'nullable|url|max:255',
        ]);

        $mail = [
            'mail_host' => trim($validated['mail_host'] ?? ''),
            'mail_port' => $validated['mail_port'] ?? 587,
            'mail_username' => $validated['mail_username'] ?? '',
            'mail_encryption' => $validated['mail_encryption'] ?? 'tls',
            'mail_from_address' => $validated['mail_from_address'] ?? '',
            'mail_from_name' => $validated['mail_from_name'] ?? '',
            'app_url' => rtrim($validated['app_url'] ?? '', '/'),
        ];

        // The password is never sent back to the browser, so a blank field means "keep what's
        // saved" rather than "erase it".
        if (filled($validated['mail_password'] ?? null)) {
            $mail['mail_password'] = Crypt::encryptString($validated['mail_password']);
        }

        $this->settingService->update($mail + [
            'company_name' => $validated['company_name'],
            'alert_emails' => $this->parseEmailList($validated['alert_emails'] ?? ''),
            'offline_timeout_minutes' => $validated['offline_timeout_minutes'],
            'preventive_lead_days' => $validated['preventive_lead_days'],
            'auto_fault_on_critical' => $request->boolean('auto_fault_on_critical'),
            'log_retention_days' => $validated['log_retention_days'],
        ]);

        return redirect()->route('settings.index')->with('success', 'Settings saved.');
    }

    /**
     * Sends one email to the signed-in user with the SAVED mail settings, so a typo in the SMTP
     * details shows up here with the server's own error message instead of as alerts that never
     * arrive.
     */
    public function testEmail(Request $request, MailSettings $mailSettings)
    {
        $mailSettings->apply();

        try {
            Mail::raw(
                'This is a test email from '.config('app.name').'. If you can read it, outgoing email is working.',
                fn ($message) => $message->to($request->user()->email)->subject('['.config('app.name').'] Test email')
            );
        } catch (\Throwable $e) {
            return redirect()->route('settings.index')->with('error', 'The test email could not be sent: '.$e->getMessage());
        }

        return redirect()->route('settings.index')->with('success', "Test email sent to {$request->user()->email}.");
    }

    /**
     * Comma/newline-separated free text -> a clean array of unique, valid-looking emails.
     */
    private function parseEmailList(string $raw): array
    {
        $emails = preg_split('/[,\n\r]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);

        return collect($emails)
            ->map(fn ($email) => trim($email))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
    }
}
