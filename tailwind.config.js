/** @type {import('tailwindcss').Config} */
const baseConfig = {
  content: [],
  theme: {
    extend: {},
  },
  plugins: [],
};

// Extend the existing configuration
baseConfig.content = [
  './assets/**/*.html',
  './assets/**/*.js',
  './assets/css/**/*.css',
];

module.exports = baseConfig;
