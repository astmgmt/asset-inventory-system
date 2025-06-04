import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    important: true, // Add this line
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                inter: ['Inter', 'sans-serif'],
                roboto: ['Roboto', 'sans-serif'],
                sans: ['Figtree', ...defaultTheme.fontFamily.sans], 
            },
            // Add backdropBlur if needed (usually included by default)
            backdropBlur: {
                sm: '4px',
            },
        },
    },

    plugins: [forms, typography],
};

// import defaultTheme from 'tailwindcss/defaultTheme';
// import forms from '@tailwindcss/forms';
// import typography from '@tailwindcss/typography';

// /** @type {import('tailwindcss').Config} */
// export default {
//     important: true,
//     content: [
//         './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
//         './vendor/laravel/jetstream/**/*.blade.php',
//         './storage/framework/views/*.php',
//         './resources/views/**/*.blade.php',
//     ],

//     theme: {
//         extend: {
//             fontFamily: {
//                 inter: ['Inter', 'sans-serif'],
//                 roboto: ['Roboto', 'sans-serif'],
//                 sans: ['Figtree', ...defaultTheme.fontFamily.sans], 
//             },
//             backgroundImage: {
//                 'auth-pattern': "url('/svg/auth-pattern.svg')",
//             },
//         },
//     },

//     plugins: [forms, typography],
// };
