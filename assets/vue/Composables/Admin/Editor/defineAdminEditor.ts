/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Déclaration d'un éditeur de texte du back-office (editor.ts du dossier de l'éditeur)
 */
import type { AdminEditor } from '@/ts/Admin/Editor/AdminEditor.type';

/**
 * Déclare un éditeur, avec typage
 * @param editor
 */
export function defineAdminEditor(editor: AdminEditor): AdminEditor {
  return editor;
}
