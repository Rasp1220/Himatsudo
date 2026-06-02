import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'
import { viteStaticCopy } from 'vite-plugin-static-copy'

export default defineConfig({
  plugins: [
    vue(),
    viteStaticCopy({
      targets: [
        { src: 'node_modules/tinymce/skins',   dest: 'tinymce' },
        { src: 'node_modules/tinymce/plugins',  dest: 'tinymce' },
        { src: 'node_modules/tinymce/icons',    dest: 'tinymce' },
        { src: 'node_modules/tinymce/models',   dest: 'tinymce' },
        { src: 'node_modules/tinymce/themes',   dest: 'tinymce' },
      ],
    }),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost:8080',
        changeOrigin: true,
      },
    },
  },
})
