import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
  plugins: [vue()],
  publicDir: false,
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
    },
  },
  server: {
    origin: 'http://localhost:5173',
  },
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: path.resolve(__dirname, 'resources/js/main.js'),
      output: {
        entryFileNames: `assets/index.js`,
        chunkFileNames: `assets/[name].js`,
        assetFileNames: `assets/index.[ext]`,
      },
    },
  },
});
