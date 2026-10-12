// Live updates over the self-hosted Laravel Reverb server (see resources/js/echo.js).
// Only Echo is loaded here: this app styles with Tailwind/daisyUI and uses Alpine from a CDN,
// so the Bootstrap/axios setup in bootstrap.js is deliberately not imported.
import './echo';
import './alerts-live';
