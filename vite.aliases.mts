/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Alias d'import partagés par Vite et Vitest : @ui vise l'API publique du design system (index.ts), @ le dossier assets,
 * @admin/ un fichier du back-office, pris dans assets/overwrite/ s'il y est surchargé
 */
import { statSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import type { Alias, Plugin } from 'vite';
import { resolveAdminPath } from './vite.adminPath.mts';

/**
 * Préfixe des imports surchargeables du back-office
 */
const ADMIN_PREFIX = '@admin/';

/**
 * Dossier des composants du back-office
 */
export const ADMIN_DIR = fileURLToPath(new URL('./assets/vue/Components/Admin', import.meta.url));

/**
 * Dossier des surcharges des composants du back-office, au même chemin que sous assets/
 */
export const ADMIN_OVERWRITE_DIR = fileURLToPath(new URL('./assets/overwrite/vue/Components/Admin', import.meta.url));

export const aliases: Alias[] = [
    {
        find: /^@ui$/,
        replacement: `${ADMIN_DIR}/Ui/index.ts`,
    },
    { find: '@', replacement: fileURLToPath(new URL('./assets', import.meta.url)) },
];

/**
 * Résolution des imports @admin/ : un fichier ajouté dans assets/overwrite/ est vu au redémarrage de Vite
 */
export function adminOverwrite(): Plugin {
    const isFile = (file: string) => statSync(file, { throwIfNoEntry: false })?.isFile() ?? false;
    return {
        name: 'natheo-admin-overwrite',
        enforce: 'pre',
        async resolveId(source, importer, options) {
            if (!source.startsWith(ADMIN_PREFIX)) {
                return null;
            }
            const file = resolveAdminPath(source.slice(ADMIN_PREFIX.length), isFile, {
                admin: ADMIN_DIR,
                overwrite: ADMIN_OVERWRITE_DIR,
            });
            return this.resolve(file, importer, { ...options, skipSelf: true });
        },
    };
}
