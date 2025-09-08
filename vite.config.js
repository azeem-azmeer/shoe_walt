// vite.config.js (project root)
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
  plugins: [
    laravel({
      // List entry files as strings — do NOT import them here
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/pdp.js', // your PDP script as a separate entry
      ],
      refresh: true,
    }),
  ],
})
