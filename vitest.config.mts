import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import { aliases } from './vite.aliases.mts';

export default defineConfig({
    plugins: [vue()],
    resolve: { alias: aliases },
    test: {
        environment: 'happy-dom',
        include: ['assets/**/__tests__/**/*.spec.ts'],
        restoreMocks: true,
        setupFiles: ['./vitest.setup.ts'],
    },
});
