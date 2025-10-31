import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/about.css',    // CSS da página Sobre
                'resources/css/contact.css',  // CSS da página Contato
                'resources/css/welcome.css'   // CSS da página Principal
            ],
            refresh: true,
        }),
    ],
});

