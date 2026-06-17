import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/passkeys.js',
                'resources/css/filament/admin/theme.css',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        // When running inside Docker (DOCKER=true), bind to all interfaces and
        // route HMR through localhost so the browser can reach the dev server.
        // Outside Docker these stay undefined → native behavior is unchanged.
        host: process.env.DOCKER ? '0.0.0.0' : undefined,
        hmr: process.env.DOCKER ? { host: 'localhost' } : undefined,
        watch: {
            // Bind-mount file events don't propagate into the Linux container on
            // macOS, so fall back to polling only when running in Docker.
            usePolling: Boolean(process.env.DOCKER),
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
