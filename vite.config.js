import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/about.css',
                'resources/css/contact.css',
                'resources/css/welcome.css',
                'resources/css/auth.css',
                'resources/css/perfil.css',
                'resources/css/dashboard.css',
                'resources/css/trees.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});