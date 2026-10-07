<?php

namespace App\Http\Controllers;

use App\Interfaces\SettingInterface;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(protected SettingInterface $settingService)
    {
        $this->middleware('permission:settings.view')->only(['index']);
        $this->middleware('permission:settings.update')->only(['update']);
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
        ]);

        $this->settingService->update([
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
