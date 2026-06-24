/** @type {import('tailwindcss').Config} */
export default {
    content: [
        'emails/**/*.html',
        'layouts/**/*.html',
        'components/**/*.html',
    ],
    theme: {
        extend: {
            fontFamily: {
                inter: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
            },
        },
    },
}
