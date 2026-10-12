{{--
    Shared shell for every 4xx/5xx page (errors/403.blade.php, errors/404.blade.php, …).

    Deliberately standalone — it does NOT extend layouts.app. That layout's view composers hit
    the database (notifications bell, company name) and read the session user, so it could not
    render on the very 500s these pages exist for, nor for a guest. Everything here is static:
    the theme bootstrap, the brand logo partial and the compiled stylesheet.

    A child view sets: code (e.g. 404), title, message. Optional: icon (an SVG path "d" value).
--}}
@php
    $statusCode = (int) trim($__env->yieldContent('code', '500'));
    $isServerError = $statusCode >= 500;
    $accent = $isServerError ? 'text-error' : 'text-warning';
    $builtAssets = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'));

    // Sessions live in the database here, so on a 500 caused by the database this check can
    // itself throw — the error page must never fail because of it.
    try {
        $signedIn = auth()->check();
    } catch (\Throwable $e) {
        $signedIn = false;
    }
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Error') ({{ $statusCode }}) — {{ config('app.name', 'LogXense Enterprise') }}</title>

    <script>
        (function () {
            var stored = 'light';
            try { stored = localStorage.getItem('theme') || 'light'; } catch (e) {}
            document.documentElement.setAttribute('data-theme', stored);
        })();
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @if($builtAssets)
        @vite(['resources/css/app.css'])
    @endif
    <style>
        .theme-logo-dark { display: none; }
        [data-theme="forest"] .theme-logo-light { display: none; }
        [data-theme="forest"] .theme-logo-dark { display: inline-block; }
        /* Readable even if the compiled stylesheet is missing. */
        body { margin: 0; font-family: Inter, system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-base-200 font-inter antialiased flex flex-col">

    <header class="px-4 sm:px-6 lg:px-8 h-16 flex items-center border-b border-base-300/40 bg-base-100/80">
        <a href="{{ url('/') }}" class="flex items-center gap-3">
            @include('partials._brand-logo', ['class' => 'h-8 w-auto object-contain'])
            <span class="text-sm font-bold leading-none">LogXense <span class="opacity-50 font-normal">Enterprise</span></span>
        </a>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="max-w-lg w-full text-center">
            <div class="mx-auto mb-6 w-20 h-20 rounded-3xl flex items-center justify-center bg-base-100 shadow-lg border border-base-300/50 {{ $accent }}">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="@yield('icon', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z')"></path>
                </svg>
            </div>

            <p class="text-7xl sm:text-8xl font-extrabold tracking-tight {{ $accent }}">{{ $statusCode }}</p>
            <h1 class="mt-4 text-2xl sm:text-3xl font-bold">@yield('title', 'Something went wrong')</h1>
            <p class="mt-3 text-base-content/70">@yield('message', 'An unexpected error occurred.')</p>

            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <button type="button" class="btn btn-ghost gap-2" onclick="if (history.length > 1) { history.back(); } else { window.location.href = '{{ url('/') }}'; }">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Go back
                </button>
                <a href="{{ url('/') }}" class="btn btn-primary gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    {{ $signedIn ? 'Go to dashboard' : 'Go to sign in' }}
                </a>
            </div>

            @hasSection('hint')
                <p class="mt-8 text-xs text-base-content/50">@yield('hint')</p>
            @endif
        </div>
    </main>

    <footer class="py-4 text-center text-xs text-base-content/40">
        {{ config('app.name', 'LogXense Enterprise') }} · Error {{ $statusCode }}
    </footer>
</body>
</html>
