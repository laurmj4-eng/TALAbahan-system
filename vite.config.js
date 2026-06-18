import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import fs from 'fs';

/**
 * Tiny plugin that writes a `public/hot` marker while the dev server is
 * running.  The PHP view checks for this file to decide whether to load
 * assets from the Vite dev server (localhost:5173) or from the production
 * build manifest.
 */
function hotFilePlugin() {
  const hotPath = path.resolve(__dirname, 'public/hot');
  return {
    name: 'vite-plugin-hot-file',
    configureServer() {
      fs.writeFileSync(hotPath, 'http://localhost:5173', 'utf-8');

      // Clean up on process exit
      const cleanup = () => {
        try { fs.unlinkSync(hotPath); } catch {}
      };
      process.on('exit', cleanup);
      process.on('SIGINT', () => { cleanup(); process.exit(); });
      process.on('SIGTERM', () => { cleanup(); process.exit(); });
    },
  };
}

export default defineConfig({
  plugins: [vue(), hotFilePlugin()],
  publicDir: false,
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
    },
  },
  server: {
    origin: 'http://localhost:5173',
    host: 'localhost',
    strictPort: true,
    hmr: {
      host: 'localhost',
    },
  },
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: 'manifest.json',
    rollupOptions: {
      input: path.resolve(__dirname, 'resources/js/main.js'),
      output: {
        entryFileNames: `assets/[name].[hash].js`,
        chunkFileNames: `assets/[name].[hash].js`,
        assetFileNames: `assets/[name].[hash][extname]`,
        manualChunks: {
          vendor: ['vue', '@inertiajs/vue3', 'axios', 'chart.js', 'lucide-vue-next']
        }
      },
    },
    sourcemap: process.env.CI_ENVIRONMENT !== 'production' ? 'inline' : false,
  },
});
