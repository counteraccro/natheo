import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import { adminOverwrite, aliases } from './vite.aliases.mts';

export default defineConfig({
    plugins: [vue(), adminOverwrite()],
    resolve: {
        alias: [...aliases, { find: '@tests', replacement: fileURLToPath(new URL('./tests/js', import.meta.url)) }],
    },
    test: {
        environment: 'happy-dom',
        include: ['tests/js/**/*.spec.ts'],
        restoreMocks: true,
        setupFiles: ['./vitest.setup.ts'],
    },
});
