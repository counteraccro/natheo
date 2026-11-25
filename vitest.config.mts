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
        // Feuilles de style jamais chargées : un <link> ajouté par un composant ne doit pas écrire d'erreur
        environmentOptions: { happyDOM: { settings: { handleDisabledFileLoadingAsSuccess: true } } },
        include: ['tests/js/**/*.spec.ts'],
        restoreMocks: true,
        setupFiles: ['./vitest.setup.ts'],
    },
});
