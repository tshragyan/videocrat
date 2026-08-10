import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

const isLocal = process.env.APP_ENV === 'DEV';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
    ],

    ...(isLocal && {server: {
        host: 'videocrat.loc',
        port: 5173,

        cors: {
            origin: 'http://videocrat.loc',
        },

        hmr: {
            host: 'videocrat.loc',
            port: 5173,
        },
    }, })
});
