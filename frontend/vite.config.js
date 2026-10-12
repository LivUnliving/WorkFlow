import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  base: '/WorkFlow/app/',
  plugins: [react()],
  server: {
    host: '0.0.0.0',
    proxy: {
      '/WorkFlow/api': {
        target: 'http://localhost',
        changeOrigin: true
      }
    }
  },
  build: {
    outDir: '../app',
    emptyOutDir: true
  }
});