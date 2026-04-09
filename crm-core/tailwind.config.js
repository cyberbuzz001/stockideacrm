import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'glass-white': 'rgba(255, 255, 255, 0.7)',
                'glass-slate': 'rgba(15, 23, 42, 0.8)',
                'pro-blue': '#2563EB',
                'deal-green': '#059669',
            },
            backdropBlur: {
                xs: '2px',
            }
        },
    },

    plugins: [forms],
};
