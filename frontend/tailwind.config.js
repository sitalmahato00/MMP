/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        // Admin brand — rgb(28, 78, 217) = #1c4ed9
        brand: {
          50:  '#eef2ff',
          100: '#e0e7ff',
          200: '#c7d2fe',
          300: '#a5b4fc',
          400: '#6272f0',
          500: '#1c4ed9',
          600: '#1a44c2',
          700: '#1538a0',
          800: '#102c7e',
          900: '#0b1f5c',
        },
        primary: {
          50:  '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
        },
        // Clear action colors for buttons and UI
        action: {
          add:    '#10B981', // green-500
          addDark:'#059669',
          edit:   '#F59E0B', // amber-500
          editDark:'#D97706',
          delete: '#EF4444', // red-500
          deleteDark:'#DC2626',
          view:   '#6366F1', // indigo-500
          viewDark:'#4F46E5',
          info:   '#14B8A6', // teal-500
          infoDark:'#0D9488',
          neutral:'#6B7280', // gray-500
        },
        // Sidebar background / surface colors
        sidebar: {
          bg:    '#0f172a', // slate-900-like
          panel: '#111827',
          text:  '#e5e7eb',
          muted: '#9CA3AF',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      keyframes: {
        ticker: {
          '0%':   { transform: 'translateX(0)' },
          '100%': { transform: 'translateX(-50%)' },
        },
      },
      animation: {
        ticker: 'ticker 40s linear infinite',
      },
    },
  },
  plugins: [],
};
