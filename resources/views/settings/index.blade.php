@extends('layouts.app')

@section('title', 'Settings')

@section('breadcrumbs')
    <li>Settings</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                System Settings
            </h1>
            <p class="text-sm opacity-70 mt-2">Configure your LogXense Enterprise preferences</p>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-error shadow-lg animate-fadeIn">
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success shadow-lg animate-fadeIn">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Theme Selection Section -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <h2 class="card-title flex items-center gap-2">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                </svg>
                Theme Selection
            </h2>
            <p class="text-sm opacity-70 mb-4">Choose your preferred color theme for the application</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <!-- Forest Theme (dark) -->
                <div class="theme-option-card card bg-base-200 border border-base-300 hover:border-primary shadow-lg hover:shadow-xl transition-all cursor-pointer" data-theme-option="forest" onclick="changeTheme('forest')">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold">Forest</h3>
                            <div class="flex gap-1">
                                <div class="badge badge-ghost badge-sm">Dark</div>
                                <div class="badge badge-primary active-badge hidden">Active</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 h-8">
                            <div class="rounded bg-[#171e29]"></div>
                            <div class="rounded bg-[#1eb854]"></div>
                            <div class="rounded bg-[#1db886]"></div>
                            <div class="rounded bg-[#1e40af]"></div>
                            <div class="rounded bg-[#7b92b2]"></div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-4 h-4 rounded-full bg-success"></div>
                            <span class="text-xs opacity-70">Dark Green Theme</span>
                        </div>
                    </div>
                </div>

                <!-- Light Theme -->
                <div class="theme-option-card card bg-base-200 border border-base-300 hover:border-primary shadow-lg hover:shadow-xl transition-all cursor-pointer" data-theme-option="light" onclick="changeTheme('light')">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold">Light</h3>
                            <div class="badge badge-primary active-badge hidden">Active</div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 h-8">
                            <div class="rounded bg-white"></div>
                            <div class="rounded bg-[#15803d]"></div>
                            <div class="rounded bg-[#0f766e]"></div>
                            <div class="rounded bg-[#0e7490]"></div>
                            <div class="rounded bg-[#f0fdf4]"></div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-4 h-4 rounded-full bg-white border"></div>
                            <span class="text-xs opacity-70">Light Green Theme</span>
                        </div>
                    </div>
                </div>

                <!-- Dark Theme -->
                <div class="theme-option-card card bg-base-200 border border-base-300 hover:border-primary shadow-lg hover:shadow-xl transition-all cursor-pointer" data-theme-option="dark" onclick="changeTheme('dark')">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold">Dark</h3>
                            <div class="badge badge-primary active-badge hidden">Active</div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 h-8">
                            <div class="rounded bg-[#1d232a]"></div>
                            <div class="rounded bg-[#661ae6]"></div>
                            <div class="rounded bg-[#d926aa]"></div>
                            <div class="rounded bg-[#1fb2a5]"></div>
                            <div class="rounded bg-[#191e24]"></div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-4 h-4 rounded-full bg-[#1d232a] border"></div>
                            <span class="text-xs opacity-70">Easy on Eyes</span>
                        </div>
                    </div>
                </div>

                <!-- Cupcake Theme -->
                <div class="theme-option-card card bg-base-200 border border-base-300 hover:border-primary shadow-lg hover:shadow-xl transition-all cursor-pointer" data-theme-option="cupcake" onclick="changeTheme('cupcake')">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold">Cupcake</h3>
                            <div class="badge badge-primary active-badge hidden">Active</div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 h-8">
                            <div class="rounded bg-[#faf7f5]"></div>
                            <div class="rounded bg-[#65c3c8]"></div>
                            <div class="rounded bg-[#ef9fbc]"></div>
                            <div class="rounded bg-[#eeaf3a]"></div>
                            <div class="rounded bg-[#e0e7f1]"></div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-4 h-4 rounded-full bg-[#65c3c8]"></div>
                            <span class="text-xs opacity-70">Soft & Sweet</span>
                        </div>
                    </div>
                </div>

                <!-- Cyberpunk Theme -->
                <div class="theme-option-card card bg-base-200 border border-base-300 hover:border-primary shadow-lg hover:shadow-xl transition-all cursor-pointer" data-theme-option="cyberpunk" onclick="changeTheme('cyberpunk')">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold">Cyberpunk</h3>
                            <div class="badge badge-primary active-badge hidden">Active</div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 h-8">
                            <div class="rounded bg-[#ffee00]"></div>
                            <div class="rounded bg-[#ff7598]"></div>
                            <div class="rounded bg-[#75d1f0]"></div>
                            <div class="rounded bg-[#c07eec]"></div>
                            <div class="rounded bg-[#ffb86b]"></div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-4 h-4 rounded-full bg-[#ff7598]"></div>
                            <span class="text-xs opacity-70">Neon Vibes</span>
                        </div>
                    </div>
                </div>

                <!-- Corporate Theme (app default) -->
                <div class="theme-option-card card bg-base-200 border border-base-300 hover:border-primary shadow-lg hover:shadow-xl transition-all cursor-pointer" data-theme-option="corporate" onclick="changeTheme('corporate')">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold">Corporate</h3>
                            <div class="flex gap-1">
                                <div class="badge badge-ghost badge-sm">Default</div>
                                <div class="badge badge-primary active-badge hidden">Active</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 h-8">
                            <div class="rounded bg-white"></div>
                            <div class="rounded bg-[#4b6bfb]"></div>
                            <div class="rounded bg-[#7b92b2]"></div>
                            <div class="rounded bg-[#67cba0]"></div>
                            <div class="rounded bg-[#e7eef6]"></div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-4 h-4 rounded-full bg-[#4b6bfb]"></div>
                            <span class="text-xs opacity-70">Professional</span>
                        </div>
                    </div>
                </div>

                <!-- Synthwave Theme -->
                <div class="theme-option-card card bg-base-200 border border-base-300 hover:border-primary shadow-lg hover:shadow-xl transition-all cursor-pointer" data-theme-option="synthwave" onclick="changeTheme('synthwave')">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold">Synthwave</h3>
                            <div class="badge badge-primary active-badge hidden">Active</div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 h-8">
                            <div class="rounded bg-[#1a103d]"></div>
                            <div class="rounded bg-[#e779c1]"></div>
                            <div class="rounded bg-[#58c7f3]"></div>
                            <div class="rounded bg-[#f9cb28]"></div>
                            <div class="rounded bg-[#221551]"></div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-4 h-4 rounded-full bg-[#e779c1]"></div>
                            <span class="text-xs opacity-70">Retro Wave</span>
                        </div>
                    </div>
                </div>

                <!-- Business Theme -->
                <div class="theme-option-card card bg-base-200 border border-base-300 hover:border-primary shadow-lg hover:shadow-xl transition-all cursor-pointer" data-theme-option="business" onclick="changeTheme('business')">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold">Business</h3>
                            <div class="badge badge-primary active-badge hidden">Active</div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 h-8">
                            <div class="rounded bg-[#1e293b]"></div>
                            <div class="rounded bg-[#1c4ed8]"></div>
                            <div class="rounded bg-[#7c3aed]"></div>
                            <div class="rounded bg-[#0891b2]"></div>
                            <div class="rounded bg-[#374151]"></div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-4 h-4 rounded-full bg-[#1c4ed8]"></div>
                            <span class="text-xs opacity-70">Formal Dark</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm">Theme changes will be applied immediately and saved to your preferences.</span>
            </div>
        </div>
    </div>

    <!-- Application Settings -->
    @php
        $alertEmailsValue = implode(PHP_EOL, $settings['alert_emails']);
        $mailPasswordSaved = filled($settings['mail_password'] ?? null);
    @endphp
    <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
    <fieldset class="contents" @cannot('settings.update') disabled @endcannot>
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Company -->
            <div class="card bg-base-100 shadow-lg border border-base-300/50">
                <div class="card-body">
                    <h2 class="card-title flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Company
                    </h2>

                    <x-form.input name="company_name" label="Company Name" required maxlength="255"
                                  :value="$settings['company_name']"
                                  help="Shown in the sidebar" />
                </div>
            </div>

            <!-- Data Retention -->
            <div class="card bg-base-100 shadow-lg border border-base-300/50">
                <div class="card-body">
                    <h2 class="card-title flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Data Retention
                    </h2>

                    <x-form.input type="number" name="log_retention_days" label="Machine Log Retention (days)" required min="1"
                                  :value="$settings['log_retention_days']"
                                  help="Sensor readings older than this are pruned daily (<code>app:generate-preventive-work-orders</code>'s sibling scheduled job, <code>model:prune</code>)" />
                </div>
            </div>
        </div>

        <!-- Alerting -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title flex items-center gap-2">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    Alerting
                </h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-2">
                    <div class="space-y-4">
                        <x-form.input type="number" name="offline_timeout_minutes" label="Offline Timeout (minutes)" required min="1"
                                      :value="$settings['offline_timeout_minutes']"
                                      help="How long a machine can go quiet before <code>app:check-offline-devices</code> raises an offline alert" />

                        @module('work-orders')
                        <x-form.input type="number" name="preventive_lead_days" label="Preventive Lead Time (days)" required min="0"
                                      :value="$settings['preventive_lead_days']"
                                      help="How far ahead of a machine's next maintenance date <code>app:generate-preventive-work-orders</code> creates one" />
                        @else
                        {{-- SettingController still requires this value, so a lite edition carries it through unchanged rather than hiding a required field. --}}
                        <input type="hidden" name="preventive_lead_days" value="{{ $settings['preventive_lead_days'] }}" />
                        @endmodule

                        <x-form.toggle name="auto_fault_on_critical" color="error"
                                        label="Auto-Fault on Critical Alert"
                                        description="Automatically set a machine's status to Fault when a threshold reading is critical"
                                        :checked="$settings['auto_fault_on_critical']" />
                    </div>

                    <x-form.textarea name="alert_emails" label="Extra Alert Recipients" :rows="6"
                                      :value="$alertEmailsValue"
                                      placeholder="one@example.com&#10;two@example.com"
                                      help="One email per line — sent every threshold/offline alert, in addition to admins and managers" />
                </div>
            </div>
        </div>

        <!-- Email (SMTP) -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title flex items-center gap-2">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Email (SMTP)
                </h2>
                <p class="text-sm opacity-70">
                    The mail server used to send alert emails. Leave the server empty to keep the server's own setup
                    (a fresh install only writes emails to its log). Save, then use <strong>Send test email</strong> to check it.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                    <x-form.input name="mail_host" label="SMTP Server" maxlength="255"
                                  :value="$settings['mail_host']" placeholder="smtp.example.com" />
                    <x-form.input type="number" name="mail_port" label="Port" min="1" max="65535"
                                  :value="$settings['mail_port']" help="Usually 587 (STARTTLS) or 465 (SSL/TLS)" />
                    <x-form.input name="mail_username" label="Username" maxlength="255" autocomplete="off"
                                  :value="$settings['mail_username']" />
                    <x-form.input type="password" name="mail_password" label="Password" maxlength="255" autocomplete="new-password"
                                  :help="$mailPasswordSaved ? 'A password is saved. Leave blank to keep it.' : null" />
                    <x-form.select name="mail_encryption" label="Encryption"
                                   :options="['tls' => 'STARTTLS (port 587)', 'ssl' => 'SSL/TLS (port 465)', 'none' => 'None / automatic']"
                                   :selected="$settings['mail_encryption']" />
                    <x-form.input type="email" name="mail_from_address" label="From Address" maxlength="255"
                                  :value="$settings['mail_from_address']" placeholder="alerts@yourcompany.com" />
                    <x-form.input name="mail_from_name" label="From Name" maxlength="255"
                                  :value="$settings['mail_from_name']" placeholder="{{ config('app.name') }}" />
                    <x-form.input type="url" name="app_url" label="Site Address" maxlength="255"
                                  :value="$settings['app_url']" placeholder="http://192.168.1.50"
                                  help="How people open this site. Used for the links inside emails" />
                </div>

                @can('settings.update')
                <div>
                    <button type="submit" form="testEmailForm" class="btn btn-outline btn-sm gap-2">Send test email to me</button>
                    <span class="text-xs opacity-60 ml-2">Sends to {{ auth()->user()->email }} using the saved settings</span>
                </div>
                @endcan
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save Changes
            </button>
        </div>
    </fieldset>
    </form>

    @can('settings.update')
    <form id="testEmailForm" action="{{ route('settings.test-email') }}" method="POST">@csrf</form>
    @endcan
</div>

<script>
// This page's picker offers all 8 DaisyUI themes, but shares the same 'theme' localStorage
// key and window.setTheme() function as the header's light/dark toggle (see
// layouts/app.blade.php) — picking anything here also updates what the header switch shows,
// and vice versa.
function changeTheme(theme) {
    window.setTheme(theme);
    markActiveTheme(theme);

    if (window.showToast) {
        window.showToast(`Theme changed to ${theme}`, 'success');
    }
}

function markActiveTheme(theme) {
    document.querySelectorAll('.theme-option-card').forEach(function (card) {
        var isActive = card.dataset.themeOption === theme;
        card.querySelector('.active-badge')?.classList.toggle('hidden', !isActive);
        card.classList.toggle('border-primary', isActive);
        card.classList.toggle('border-2', isActive);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    var savedTheme = localStorage.getItem('theme') || 'light';
    markActiveTheme(savedTheme);
});
</script>
@endsection
