import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'sc-orange-1': '#B08968',
                'sc-orange-5': '#7F5539',
                'sc-orange-9': '#592C1C',
                'sc-orange-11': '#FF600A',
                'sc-beige-9': '#CCAC8F',
                'sc-beige-5': '#F0E5DB',
                'sc-beige-1': '#FCF7F0',
                'sc-grey-1': '#E7E0DA',
                'sc-grey-9': '#4B3D30',
                'sc-bg-1': '#FCF7F0',
                'sc-headfoot-1': '#FFFFFF',

                'scdefault-100': '#E5D5C6',
                'scdefault-200': '#E5D5C6',
                'scdefault-300': '#E5D5C6',
                'scdefault-400': '#D8C1AB',
                'scdefault-500': '#D8C1AB',
                'scdefault-600': '#D8C1AB',
                'scdefault-700': '#CCAC8F',
                'scdefault-800': '#CCAC8F',
                'scdefault-900': '#CCAC8F',

                'primary-100': '#E5D5C6',
                'primary-200': '#E5D5C6',
                'primary-300': '#E5D5C6',
                'primary-400': '#D8C1AB',
                'primary-500': '#D8C1AB',
                'primary-600': '#D8C1AB',
                'primary-700': '#CCAC8F',
                'primary-800': '#CCAC8F',
                'primary-900': '#CCAC8F',

                'gray-800': '#4B3D30'
            },
        },
    },

    plugins: [forms, typography, require('flowbite/plugin')],
};
