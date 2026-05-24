import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
  base: process.env.NODE_ENV === 'production' ? '/spa/' : '/',
  plugins: [react()],
  resolve: {
    alias: {
      '@':           path.resolve(__dirname, './src'),
      '@app':        path.resolve(__dirname, './src/app'),
      '@shared':     path.resolve(__dirname, './src/shared'),
      '@modules':    path.resolve(__dirname, './src/modules'),
      '@components': path.resolve(__dirname, './src/shared/components'),
      '@layouts':    path.resolve(__dirname, './src/app/layouts'),
      '@services':   path.resolve(__dirname, './src/shared/services'),
      '@store':      path.resolve(__dirname, './src/app/store'),
      '@hooks':      path.resolve(__dirname, './src/shared/hooks'),
      '@auth':       path.resolve(__dirname, './src/app/router'),
      '@api':        path.resolve(__dirname, './src/shared/api'),
      '@assets':     path.resolve(__dirname, './src/assets'),
      '@styles':     path.resolve(__dirname, './src/styles'),
      // legacy shims so unedited module files still resolve
      '@utils':      path.resolve(__dirname, './src/shared/utils'),
    },
  },
  server: {
    port: 3000,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
      '/brand-logo': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
      '/storage': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
  build: {
    outDir: '../public/spa',
    emptyOutDir: true,
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['react', 'react-dom', 'react-router-dom'],
          redux: ['@reduxjs/toolkit', 'react-redux'],
          query: ['@tanstack/react-query'],
          charts: ['recharts'],
        },
      },
    },
  },
});
