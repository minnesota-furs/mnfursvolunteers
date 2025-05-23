import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

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
            screens: {
                print: { raw: 'print' },
                screen: { raw: 'screen' },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'brand-green'       : '#007848',
                'brand-lightgreen'  : '#e3eca9',
                'brand-lightbeige'  : '#efe8e1',
                'brand-brown'       : '#44392b',
              },
        },
    },

    plugins: [forms, typography],
};
