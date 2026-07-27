/** @type {import('@maizzle/framework').Config} */
export default {
    build: {
        output: {
            path: '../resources/views/emails',
            extension: 'blade.php',
        },
    },
    css: {
        inline: true,
        purge: true,
        shorthand: true,
    },
    prettify: true,
}
