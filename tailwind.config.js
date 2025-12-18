// tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'

export default {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
    './resources/**/*.vue',
    './storage/framework/views/*.php',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './public/**/*.html'
  ],

  // Prevent purge from stripping overlay controls & arbitrary values used in the media library
  safelist: [
    // existing
    'max-h-[88vh]',
    'aspect-[16/9]',

    // delete-button overlay (top-right “X”)
    'absolute',
    'top-2',
    'right-2',
    'z-20',
    'rounded-full',
    'p-1',
    'bg-red-600',
    'hover:bg-red-700',
    'text-white',
    'pointer-events-auto',
    'h-3',
    'w-3',
    'text-[10px]',

    // brand-colored actions in the card footer (arbitrary values)
    'text-[#105702]',
    'bg-[#105702]',
    'border-[#105702]/30',
    'hover:bg-[#105702]/10',
  ],

  theme: {
    container: {
      center: true,
      padding: { DEFAULT: '1rem', sm: '1.5rem', lg: '2rem' },
      screens: { '2xl': '1400px' },
    },
    extend: {
      colors: { brand: '#1A8700' },
      typography: ({ theme }) => ({
        DEFAULT: {
          css: {
            /* headings */
            h1: { fontWeight: '800' },
            h2: { fontWeight: '700' },
            h3: { fontWeight: '600' },
            'h1,h2,h3,h4': { color: theme('colors.slate.900') },

            /* links: inherit weight so <strong><a>...</a></strong> becomes bold */
            a: {
              color: theme('colors.emerald.700'),
              fontWeight: 'inherit', // ← key fix
            },

            /* strong/b inside and around links */
            strong: { fontWeight: '700', color: 'inherit' },
            b:      { fontWeight: '700', color: 'inherit' },
            'a strong, a b, strong a, b a': { fontWeight: '700' },
          },
        },
      }),
    },
  },

  plugins: [forms, typography],
}
