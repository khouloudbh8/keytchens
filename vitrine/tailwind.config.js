/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
  "*.html",
  "./en/*.html",
  "./src/**/*.html",
  "./src/**/*.js",
  "./node_modules/flowbite/**/*.js"
],
  theme: {
    fontFamily: {
    },
    screens: {
      'sm': '640px',
      // => @media (min-width: 640px) { ... }

      'md': '768px',
      // => @media (min-width: 768px) { ... }

      'lg': '1024px',
      // => @media (min-width: 1024px) { ... }

      'xl': '1280px',
      // => @media (min-width: 1280px) { ... }

      '2xl': '1536px',
      // => @media (min-width: 1536px) { ... }
      '3xl': '5000px'
      // => @media (min-width: 2000px) { ... }

    },
    extend: {
      colors: {
        primary: '#00C09E',
        secondary: '#FFC120',
        midnight: '#18143B',
        darkblue:'#121126'
      },
      backgroundImage: {
        'hero': "url('/images/font-section-1.jpg')",
        'platform-pattern': "linear-gradient(to right bottom, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.4)), url('/images/section-3.png')",
      },
    },
  },
  plugins: [
    require('flowbite/plugin')
  ],
}
