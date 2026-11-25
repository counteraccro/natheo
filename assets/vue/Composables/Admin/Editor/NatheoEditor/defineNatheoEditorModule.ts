/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Déclaration d'un module de Natheo-Editor (module.ts du dossier du module)
 */
import type { NatheoEditorModule } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

/**
 * Déclare un module, avec typage
 * @param module
 */
export function defineNatheoEditorModule(module: NatheoEditorModule): NatheoEditorModule {
  return module;
}
