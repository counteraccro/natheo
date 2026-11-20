import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import { adminOverwrite, aliases } from './vite.aliases.mts';

export default defineConfig({
    plugins: [vue(), adminOverwrite()],
    resolve: { alias: aliases },
    test: {
        environment: 'happy-dom',
        include: ['assets/**/__tests__/**/*.spec.ts'],
        restoreMocks: true,
        setupFiles: ['./vitest.setup.ts'],
    },
});
