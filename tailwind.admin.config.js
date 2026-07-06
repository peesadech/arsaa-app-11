import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import aspectRatio from '@tailwindcss/aspect-ratio';

/**
 * Config for the NEW design system (myTripsBackend style).
 * Used by resources/css/admin.css via the `@config` directive.
 * Preflight stays ENABLED here (matches the previous CDN default).
 *
 * @type {import('tailwindcss').Config}
 */
export default {
    content: [
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            colors: {
                brand: {
                    50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd',
                    400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8',
                    800: '#1e40af', 900: '#1e3a8a',
                },
                surface: {
                    50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1',
                },
            },
            boxShadow: {
                card: '0 1px 2px 0 rgb(0 0 0 / 0.04), 0 1px 3px 0 rgb(0 0 0 / 0.06)',
                soft: '0 2px 6px 0 rgb(15 23 42 / 0.06)',
            },
            borderRadius: {
                xl: '0.9rem',
                '2xl': '1.1rem',
            },
        },
    },
    plugins: [forms, typography, aspectRatio],
};
