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
        // ESTO ES LO QUE SOLUCIONA TU ERROR:
        host: '0.0.0.0', // Escucha en todas las interfaces internas del contenedor
        port: 5173,      // Puerto estándar de Vite
        hmr: {
            host: '172.16.60.18', // Dirección que usará el navegador para conectar
        },
    },
});