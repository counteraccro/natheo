import { defineConfig } from 'vitest/config';
import { fileURLToPath } from 'node:url';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@ui': fileURLToPath(new URL('./assets/vue/Components/Admin/Ui', import.meta.url)),
      '@': fileURLToPath(new URL('./assets', import.meta.url)),
    },
  },
  test: {
    environment: 'happy-dom',
    include: ['assets/**/__tests__/**/*.spec.ts'],
    restoreMocks: true,
  },
});
