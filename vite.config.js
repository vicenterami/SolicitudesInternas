import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '172.16.60.18', // Tu IP real
        hmr: {
            host: '172.16.60.18', // Obliga al celular a buscar aquí los cambios
        },
        port: 5173,
    },
});