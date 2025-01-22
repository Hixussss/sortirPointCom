/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './templates/*.html',      // Fichiers HTML (Twig)
    './templates/**/*.html.twig', // Fichiers Twig (Symfony)
    './assets/**/*.js',           // Fichiers JavaScript
    './assets/**/*.css',          // Fichiers CSS
  ],
  darkMode: ['class'],
  theme: {

      container: {
          center: true,
          padding: {
              DEFAULT: '1rem',
              sm: '2rem',
              lg: '4rem',
              xl: '5rem',
              '2xl': '10rem',
          },
      },

      fontFamily: {
          'nunito': ['"Nunito"', 'sans-serif'],
          'maven': ['"Maven Pro"', 'sans-serif'],
      },

      extend: {
          colors: {
              'primary': "#007bff",
          },
      },
  },

  plugins: [
      require('preline/plugin'),
      require('@tailwindcss/forms'),
  ],
};
