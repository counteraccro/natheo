/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Chargement à la demande du thème du back-office choisi par l'utilisateur, miniatures de tous les thèmes
 */
import type { Component } from 'vue';
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
  return byThemeFolder('theme.ts', core, overwrite);
}

/**
 * Fichiers d'un même nom indexés par dossier de thème, ceux de la surcharge remplaçant ceux du cœur
 * @param file nom du fichier dans le dossier du thème
 * @param core résultat de import.meta.glob sur Components/Admin/Themes/
 * @param overwrite résultat de import.meta.glob sur overwrite/vue/Components/Admin/Themes/
 */
export function byThemeFolder<V>(
  file: string,
  core: Record<string, V>,
  overwrite: Record<string, V> = {}
): Record<string, V> {
  const byFolder: Record<string, V> = {};
  for (const entries of [core, overwrite]) {
    for (const [path, value] of Object.entries(entries)) {
      const match = path.match(/\/Themes\/([^/]+)\/([^/]+)$/);
      if (match !== null && match[2] === file) {
        byFolder[match[1]] = value;
      }
    }
  }
  return byFolder;
}

/** Miniatures des thèmes (Thumbnail.vue du dossier du thème), montrées dans le choix de la disposition */
const thumbnails = byThemeFolder<Component>(
  'Thumbnail.vue',
  import.meta.glob<Component>('../../Components/Admin/Themes/*/Thumbnail.vue', { eager: true, import: 'default' }),
  import.meta.glob<Component>('../../../overwrite/vue/Components/Admin/Themes/*/Thumbnail.vue', {
    eager: true,
    import: 'default',
  })
);

/**
 * Miniature d'un thème, undefined s'il n'en fournit pas
 * @param id
 */
export function themeThumbnail(id: string): Component | undefined {
  return thumbnails[themeFolder(id)];
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
