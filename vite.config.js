import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite'; // <-- Thêm dòng import này

export default defineConfig({
    plugins: [
        tailwindcss(), // <-- Thêm plugin này nằm TRƯỚC laravel()
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});