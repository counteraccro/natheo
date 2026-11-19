/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Alias d'import partagés par Vite et Vitest : @ui vise l'API publique du design system (index.ts), @ le dossier assets
 */
import { fileURLToPath } from 'node:url';
import type { Alias } from 'vite';

export const aliases: Alias[] = [
    {
        find: /^@ui$/,
        replacement: fileURLToPath(new URL('./assets/vue/Components/Admin/Ui/index.ts', import.meta.url)),
    },
    { find: '@', replacement: fileURLToPath(new URL('./assets', import.meta.url)) },
];
