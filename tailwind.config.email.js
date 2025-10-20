/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/views/mail/**/*.blade.php',   // hanya scan view email
  ],
  theme: {
    extend: {},
  },
  corePlugins: {
    container: false, // tidak perlu di email
  }
}
