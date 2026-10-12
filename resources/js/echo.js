import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Self-hosted Reverb (no third-party service). VITE_REVERB_HOST is blank by default so the
// browser connects to whichever host the page itself was opened from — the same build works
// at http://192.168.1.50, http://localhost or a hostname. Without an app key (live updates
// not configured) Echo is simply not created and the machine page falls back to polling.
if (import.meta.env.VITE_REVERB_APP_KEY) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    // Alpine (loaded from a CDN) can start before this deferred module has run, so the
    // machine page listens for this instead of assuming window.Echo already exists.
    window.dispatchEvent(new Event('echo:ready'));
}