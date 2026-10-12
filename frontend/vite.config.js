import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
export default defineConfig({
  base: '/gestao_freelas/app/',
  plugins: [react()],
  server: {
    host: '0.0.0.0',
    proxy: { '/gestao_freelas/api': { target: 'http://localhost', changeOrigin: true } }
  },
  build: { outDir: '../app', emptyOutDir: true }
});
