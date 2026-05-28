/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        // ── Nepal Government Portal Color System ──────────────────
        // Primary navy — #0B2E6B (deep government navy)
        brand: {
          50:  '#EEF3FB',
          100: '#D5E2F5',
          200: '#AABFEB',
          300: '#7A9ADE',
          400: '#4A74CF',
          500: '#1D4ED8', // Royal Blue
          600: '#1A44C2',
          700: '#1538A0',
          800: '#0F2C7E',
          900: '#0B2E6B', // Navy Blue (primary)
          950: '#071D47',
        },
        primary: {
          50:  '#EEF3FB',
          100: '#D5E2F5',
          200: '#AABFEB',
          300: '#7A9ADE',
          400: '#4A74CF',
          500: '#1D4ED8',
          600: '#1A44C2',
          700: '#1538A0',
          800: '#0F2C7E',
          900: '#0B2E6B',
        },
        // Government portal surface colors
        gov: {
          navy:    '#0B2E6B', // Dark navy — sidebar, topbar
          royal:   '#1D4ED8', // Royal blue — accents, active states
          surface: '#F4F7FB', // Light gray — page background
          border:  '#DCE3EB', // Border gray
          white:   '#FFFFFF',
          muted:   '#6B7A8D', // Muted text
          dark:    '#1A2B45', // Dark text
        },
        // Clear action colors for buttons and UI
        action: {
          add:       '#16A34A', // green-600
          addDark:   '#15803D',
          edit:      '#D97706', // amber-600
          editDark:  '#B45309',
          delete:    '#DC2626', // red-600
          deleteDark:'#B91C1C',
          view:      '#1D4ED8', // royal blue (gov style)
          viewDark:  '#1538A0',
          info:      '#0891B2', // cyan-600
          infoDark:  '#0E7490',
          neutral:   '#6B7280',
        },
        // Sidebar — dark navy government style
        sidebar: {
          bg:    '#0B2E6B', // gov navy
          panel: '#0A2660',
          hover: '#0F3580',
          active:'#1D4ED8',
          text:  '#E8EEF8',
          muted: '#8FA3C8',
          border:'#1A3D7A',
        },
      },
      fontFamily: {
        sans: ['Inter', 'Noto Sans', 'system-ui', 'sans-serif'],
      },
      fontSize: {
        '2xs': ['0.625rem', { lineHeight: '0.875rem' }],
      },
      borderRadius: {
        'gov': '3px',   // Very small radius — government style
        'gov-md': '4px',
        'gov-lg': '6px',
      },
      boxShadow: {
        'gov':    '0 1px 3px 0 rgba(11,46,107,0.08), 0 1px 2px -1px rgba(11,46,107,0.06)',
        'gov-md': '0 2px 6px 0 rgba(11,46,107,0.10), 0 1px 3px -1px rgba(11,46,107,0.08)',
        'gov-lg': '0 4px 12px 0 rgba(11,46,107,0.12), 0 2px 6px -2px rgba(11,46,107,0.08)',
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
