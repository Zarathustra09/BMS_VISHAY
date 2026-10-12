// javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // Dev server only (`npm run dev`). Since Vite 6 it answers CORS requests from localhost origins
        // alone, but this app is opened by its LAN address (http://192.168.x.x) so ESP32/NodeMCU and other
        // PCs can reach it. The page then loads its module scripts cross-origin from the dev server, and
        // without this the browser silently refuses them: no Echo, no live chart, no live alerts - while the
        // stylesheet (not CORS-checked) still loads, so the page looks fine. Has no effect on `npm run build`.
        cors: true,
    },
});
