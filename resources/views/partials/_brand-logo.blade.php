{{--
    Theme-aware logo: renders both assets, CSS (defined in layouts/guest/app.blade.php) shows
    only the one that's visible against the current theme's bg-base-100. Pass $class for sizing.
--}}
@php $class = $class ?? 'h-8 w-auto object-contain'; @endphp
<img src="{{ asset('assets/images/logo-black.png') }}" alt="LogXense Enterprise" class="theme-logo-light {{ $class }}">
<img src="{{ asset('assets/images/logo-white.png') }}" alt="LogXense Enterprise" class="theme-logo-dark {{ $class }}">
