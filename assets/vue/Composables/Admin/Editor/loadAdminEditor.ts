/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Chargement à la demande de l'éditeur de texte actif : celui de la surcharge s'il y en a un, Natheo-Editor sinon
 */
import type { AdminEditor } from '@/ts/Admin/Editor/AdminEditor.type';

/** Dossier de l'éditeur du cœur */
export const DEFAULT_ADMIN_EDITOR = 'NatheoEditor';

/** Chargeurs des editor.ts, indexés par le chemin du fichier (résultat de import.meta.glob) */
export type AdminEditorLoaders = Record<string, () => Promise<AdminEditor>>;

/**
 * Chargeur de l'éditeur actif
 * @param core chargeurs de Components/Admin/Editors/
 * @param overwrite chargeurs de overwrite/vue/Components/Admin/Editors/
 */
export function pickAdminEditor(
  core: AdminEditorLoaders,
  overwrite: AdminEditorLoaders = {}
): () => Promise<AdminEditor> {
  const own = Object.entries(overwrite);
  if (own.length > 1) {
    throw new Error(`Un seul éditeur est permis dans la surcharge : ${own.map(([path]) => path).join(', ')}`);
  }
  if (own.length === 1) {
    return own[0][1];
  }
  const loader = Object.entries(core).find(([path]) => path.includes(`/Editors/${DEFAULT_ADMIN_EDITOR}/`))?.[1];
  if (loader === undefined) {
    throw new Error('Éditeur de texte du back-office introuvable');
  }
  return loader;
}

let active: Promise<AdminEditor> | null = null;

/**
 * Éditeur actif, téléchargé une seule fois pour tous les champs ; un échec permet un nouvel essai
 */
export function loadAdminEditor(): Promise<AdminEditor> {
  active ??= Promise.resolve()
    .then(() =>
      pickAdminEditor(
        import.meta.glob<AdminEditor>('../../../Components/Admin/Editors/*/editor.ts', { import: 'default' }),
        import.meta.glob<AdminEditor>('../../../../overwrite/vue/Components/Admin/Editors/*/editor.ts', {
          import: 'default',
        })
      )()
    )
    .catch((error: unknown) => {
      active = null;
      throw error;
    });
  return active;
}
