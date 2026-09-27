<?php

namespace App\Http\Controllers;

use App\Interfaces\AlertInterface;
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
            'thresholds' => 'array',
            'thresholds.*.warning_min' => 'nullable|numeric',
            'thresholds.*.warning_max' => 'nullable|numeric',
            'thresholds.*.critical_min' => 'nullable|numeric',
            'thresholds.*.critical_max' => 'nullable|numeric',
        ]);

        $this->settingService->update([
            'company_name' => $validated['company_name'],
            'alert_emails' => $this->parseEmailList($validated['alert_emails'] ?? ''),
            'offline_timeout_minutes' => $validated['offline_timeout_minutes'],
            'preventive_lead_days' => $validated['preventive_lead_days'],
            'auto_fault_on_critical' => $request->boolean('auto_fault_on_critical'),
            'log_retention_days' => $validated['log_retention_days'],
            'default_thresholds' => $this->parseDefaultThresholds($validated['thresholds'] ?? []),
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

    /**
     * Drops a metric entirely when all four bounds are empty — mirrors
     * AlertService::saveThresholds()'s per-machine version of the same rule.
     */
    private function parseDefaultThresholds(array $thresholds): array
    {
        $result = [];

        foreach (AlertInterface::METRICS as $metric) {
            $row = $thresholds[$metric] ?? [];

            $bounds = [
                'warning_min' => $this->nullableNumber($row['warning_min'] ?? null),
                'warning_max' => $this->nullableNumber($row['warning_max'] ?? null),
                'critical_min' => $this->nullableNumber($row['critical_min'] ?? null),
                'critical_max' => $this->nullableNumber($row['critical_max'] ?? null),
            ];

            if (collect($bounds)->every(fn ($value) => $value === null)) {
                continue;
            }

            $result[$metric] = $bounds;
        }

        return $result;
    }

    private function nullableNumber(mixed $value): ?float
    {
        return ($value === null || $value === '') ? null : (float) $value;
    }
}
