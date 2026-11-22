/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Chargement à la demande du thème du back-office choisi par l'utilisateur
 */
import type { AdminTheme } from '@/ts/Admin/Theme/AdminTheme.type';

/** Thème utilisé pour un identifiant inconnu */
export const DEFAULT_ADMIN_THEME = 'natheo_studio';

/** Chargeurs des theme.ts, indexés par le chemin du fichier (résultat de import.meta.glob) */
export type AdminThemeLoaders = Record<string, () => Promise<AdminTheme>>;

/**
 * Dossier d'un thème d'après son identifiant : natheo_studio → NatheoStudio
 * @param id
 */
export function themeFolder(id: string): string {
  return id
    .split('_')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join('');
}

/**
 * Chargeurs indexés par dossier de thème, ceux de la surcharge remplaçant ceux du cœur
 * @param core chargeurs de Components/Admin/Themes/
 * @param overwrite chargeurs de overwrite/vue/Components/Admin/Themes/
 */
export function themeLoaders(core: AdminThemeLoaders, overwrite: AdminThemeLoaders = {}): AdminThemeLoaders {
  const byFolder: AdminThemeLoaders = {};
  for (const loaders of [core, overwrite]) {
    for (const [path, loader] of Object.entries(loaders)) {
      const folder = path.match(/\/Themes\/([^/]+)\/theme\.ts$/)?.[1];
      if (folder !== undefined) {
        byFolder[folder] = loader;
      }
    }
  }
  return byFolder;
}

/**
 * Thème de l'identifiant, le thème par défaut s'il n'existe pas
 * @param id
 * @param loaders chargeurs indexés par dossier (themeLoaders())
 */
export async function loadAdminTheme(id: string, loaders: AdminThemeLoaders): Promise<AdminTheme> {
  const loader = loaders[themeFolder(id)] ?? loaders[themeFolder(DEFAULT_ADMIN_THEME)];
  if (loader === undefined) {
    throw new Error(`Thème du back-office introuvable : ${id}`);
  }
  return loader();
}
