/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  safelist: [
    {
      pattern: /grid-cols-(1|2|3|4|5|6|7|8|9|10|11|12)/,
    },
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Cygre', 'sans-serif'],
        cygre: ['Cygre', 'sans-serif'],
      },
      colors: {
        // Основные цвета КубГТУ (по дизайну kubstu.ru)
        'kubgtu-blue': {
          DEFAULT: '#3157DD',
          light: '#4163DF',
          dark: '#1E3A8A',
        },
        'kubgtu-cyan': {
          DEFAULT: '#9BD1FF',
          light: '#B8D4FF',
        },
        'kubgtu-white': '#FFFFFF',
        'kubgtu-gray': {
          light: '#F4F4F4',
          DEFAULT: '#E5E5E5',
          medium: '#CCCCCC',
          dark: '#808080',
          'blue-gray': '#9198E5',
        },
        // Семантические цвета
        primary: {
          DEFAULT: '#3157DD',
          light: '#4163DF',
          dark: '#1E3A8A',
        },
        accent: {
          DEFAULT: '#9BD1FF',
          light: '#B8D4FF',
        },
        background: '#FFFFFF',
        surface: {
          DEFAULT: '#F4F4F4',
          hover: '#E5E5E5',
        },
        text: {
          primary: '#000000',
          secondary: '#404040',
          muted: '#808080',
        },
        border: {
          DEFAULT: '#CCCCCC',
          light: '#E5E5E5',
        },
      },
      backgroundImage: {
        'gradient-kubgtu': 'linear-gradient(to right, #FFFFFF, #B8D4FF, #9BD1FF, #4163DF, #3157DD)',
        'gradient-kubgtu-vertical': 'linear-gradient(to bottom, #FFFFFF, #B8D4FF, #9BD1FF, #4163DF, #3157DD)',
        'gradient-kubgtu-overlay': 'linear-gradient(rgba(0,0,0,0.01), rgba(145,152,229,0.9))',
      },
    },
  },
  plugins: [],
}
