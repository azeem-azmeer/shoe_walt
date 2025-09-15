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
        'resources/js/user-index.js',
        'resources/js/user-productpreview.js', // your PDP script as a separate entry
        'resources/js/user-wishlist.js',
        'resources/js/admin-product-create.js',
        'resources/js/admin-products.js',
        'resources/js/admin-product-edit.js',
        'resources/js/user-cart.js',
        'resources/js/user-review.js',
      ],
      refresh: true,
    }),
  ],
})
