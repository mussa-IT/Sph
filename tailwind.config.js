/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.jsx",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#7c3aed',
        secondary: '#2563eb',
        background: '#ffffff',
        'background-secondary': '#f8fafc',
        foreground: '#0f172a',
        muted: '#64748b',
        'muted-foreground': '#475569',
        'background-dark': '#0f172a',
        'background-secondary-dark': '#1e293b',
        'foreground-dark': '#f8fafc',
        'muted-dark': '#64748b',
        'muted-foreground-dark': '#cbd5e1',
      },
      borderRadius: {
        '2xl': '1rem',
        '3xl': '1.5rem',
      },
      boxShadow: {
        'soft': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
        'card': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
        'premium': '0 18px 40px -20px rgba(15, 23, 42, 0.35), 0 8px 18px -12px rgba(124, 58, 237, 0.3)',
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
  darkMode: 'class',
}
