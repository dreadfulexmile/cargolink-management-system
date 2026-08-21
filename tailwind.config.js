import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Brand navy sampled from the CLCB logo mark (#18226b).
                brand: {
                    50: '#f5f6fa',
                    100: '#e8e9f3',
                    200: '#d3d6e8',
                    300: '#aab1e3',
                    400: '#6573dc',
                    500: '#2433a1',
                    600: '#18226b',
                    700: '#131b53',
                    800: '#0e1440',
                    900: '#0b0f30',
                    950: '#070a1e',
                },
            },
        },
    },

    plugins: [forms],
};
