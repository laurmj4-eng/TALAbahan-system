import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
  base: '/build/',
  plugins: [vue()],
  publicDir: false,
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
    },
  },
  server: {
    origin: 'http://localhost:5173',
    host: '0.0.0.0', // Listen on all interfaces for Docker
    port: 5173,
    strictPort: false,
    hmr: {
      host: 'localhost',
      protocol: 'ws',
    },
  },
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: 'manifest.json', // Generate manifest for asset versioning
    rollupOptions: {
      input: path.resolve(__dirname, 'resources/js/main.js'),
      output: {
        // Add content hash to filenames for cache busting
        entryFileNames: `assets/[name].[hash].js`,
        chunkFileNames: `assets/[name].[hash].js`,
        assetFileNames: `assets/[name].[hash][extname]`,
      },
    },
    // Generate source maps in production for error tracking
    sourcemap: process.env.CI_ENVIRONMENT !== 'production' ? 'inline' : false,
  },
});
