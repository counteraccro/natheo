/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Rendu de Natheo-Editor propre à chaque thème du back-office (Themes/<Theme>/Editor/NatheoEditor.vue)
 */
import type { Component } from 'vue';
import { DEFAULT_ADMIN_THEME, themeFolder } from '@/vue/Composables/Admin/loadAdminTheme';

/** Chargeurs des rendus, indexés par le chemin du fichier (résultat de import.meta.glob) */
export type NatheoEditorRenderLoaders = Record<string, () => Promise<Component>>;

/**
 * Chargeur du rendu d'un thème, ceux de la surcharge d'abord, celui du thème par défaut pour un thème sans rendu
 * @param theme identifiant du thème
 * @param core chargeurs de Components/Admin/Themes/
 * @param overwrite chargeurs de overwrite/vue/Components/Admin/Themes/
 */
export function pickNatheoEditorRender(
  theme: string,
  core: NatheoEditorRenderLoaders,
  overwrite: NatheoEditorRenderLoaders = {}
): () => Promise<Component> {
  const byFolder: NatheoEditorRenderLoaders = {};
  for (const loaders of [core, overwrite]) {
    for (const [path, loader] of Object.entries(loaders)) {
      const folder = /\/Themes\/([^/]+)\/Editor\/NatheoEditor\.vue$/.exec(path)?.[1];
      if (folder !== undefined) byFolder[folder] = loader;
    }
  }
  const loader = byFolder[themeFolder(theme)] ?? byFolder[themeFolder(DEFAULT_ADMIN_THEME)];
  if (loader === undefined) {
    throw new Error(`Rendu de Natheo-Editor introuvable pour le thème ${theme}`);
  }
  return loader;
}

/**
 * Chargeur du rendu de Natheo-Editor pour un thème
 * @param theme
 */
export function natheoEditorRender(theme: string): () => Promise<Component> {
  return pickNatheoEditorRender(
    theme,
    import.meta.glob<Component>('../../../../Components/Admin/Themes/*/Editor/NatheoEditor.vue', {
      import: 'default',
    }),
    import.meta.glob<Component>('../../../../../overwrite/vue/Components/Admin/Themes/*/Editor/NatheoEditor.vue', {
      import: 'default',
    })
  );
}
