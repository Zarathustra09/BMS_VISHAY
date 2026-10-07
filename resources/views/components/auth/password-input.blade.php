@props([
    'name' => 'password',
    'id' => null,
    'label' => 'Password',
    'autocomplete' => 'current-password',
    'placeholder' => '••••••••',
    'icon' => 'lock',
    'iconClass' => 'w-4 h-4 opacity-40 flex-shrink-0',
    'inputClass' => 'grow text-sm',
])

@php
    $inputId = $id ?? $name;
    $iconPath = $icon === 'shield'
        ? 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'
        : 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z';
    $lowerLabel = \Illuminate\Support\Str::lower($label);
@endphp

<label {{ $attributes->class(['input flex items-center']) }}>
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"></path>
    </svg>

    <input id="{{ $inputId }}" type="password" name="{{ $name }}" required
           autocomplete="{{ $autocomplete }}" class="{{ $inputClass }}"
           placeholder="{{ $placeholder }}" />

    <button type="button"
            class="btn btn-ghost btn-xs btn-circle opacity-50 hover:opacity-100 flex-shrink-0"
            data-password-toggle="{{ $inputId }}"
            data-password-label="{{ $lowerLabel }}"
            aria-controls="{{ $inputId }}"
            aria-pressed="false"
            aria-label="Show {{ $lowerLabel }}">
        {{-- Eye: shown while the value is masked --}}
        <svg data-toggle-icon="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>
        {{-- Eye-off: shown while the value is revealed --}}
        <svg data-toggle-icon="hide" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
        </svg>
    </button>
</label>

@once
<script>
    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('[data-password-toggle]');
        if (!toggle) { return; }

        var field = document.getElementById(toggle.dataset.passwordToggle);
        if (!field) { return; }

        var reveal = field.type === 'password';
        field.type = reveal ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', reveal ? 'true' : 'false');
        toggle.setAttribute('aria-label', (reveal ? 'Hide ' : 'Show ') + toggle.dataset.passwordLabel);
        toggle.querySelector('[data-toggle-icon="show"]').classList.toggle('hidden', reveal);
        toggle.querySelector('[data-toggle-icon="hide"]').classList.toggle('hidden', !reveal);
    });
</script>
@endonce
