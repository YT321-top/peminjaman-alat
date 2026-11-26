/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        brand: {
          red: '#E53935',
          'red-hover': '#C62828',
          50: '#FEF2F2',
          100: '#FEE2E2',
          200: '#FECACA',
          500: '#EF4444',
          600: '#DC2626',
          700: '#B91C1C',
          800: '#991B1B',
          900: '#7F1D1D',
        },
        surface: {
          primary: '#FFFFFF',
          secondary: '#F7F7F8',
        },
        ink: {
          primary: '#1F2937',
          secondary: '#6B7280',
        },
        line: {
          DEFAULT: '#E5E7EB',
        },
      },
    },
  },
  plugins: [],
};
