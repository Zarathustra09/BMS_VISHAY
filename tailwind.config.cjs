// ...new file...
module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('daisyui'),
  ],
  daisyui: {
    // include the 'forest' theme and keep some useful defaults
    themes: [
      'forest',
      'cupcake',
      'dark',
      'light'
    ],
  },
};

