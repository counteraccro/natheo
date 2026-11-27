/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module clearFormatting de Natheo-Editor : retire la mise en forme du texte sélectionné (gras, italique, barré,
 * code), en gardant les liens et les blocs
 */
import { Extension, type Editor } from '@tiptap/core';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';

/** Marques gardées : un lien fait partie du contenu */
const KEPT_MARKS = ['link'];

/**
 * Marques de mise en forme du schéma
 * @param editor
 */
const formattingMarks = (editor: Editor): string[] =>
  Object.keys(editor.schema.marks).filter((name) => !KEPT_MARKS.includes(name));

/**
 * La sélection, ou la position du curseur, porte une marque de mise en forme
 * @param editor
 */
export function hasFormatting(editor: Editor): boolean {
  const { from, to, empty, $from } = editor.state.selection;
  const marks = formattingMarks(editor).map((name) => editor.schema.marks[name]);
  if (empty) {
    const current = editor.state.storedMarks ?? $from.marks();
    return current.some((mark) => marks.includes(mark.type));
  }
  return marks.some((type) => editor.state.doc.rangeHasMark(from, to, type));
}

/**
 * Retire les marques de mise en forme de la sélection, ou du mot sous le curseur
 * @param editor
 */
export function clearFormatting(editor: Editor): boolean {
  const chain = editor.chain().focus();
  for (const name of formattingMarks(editor)) chain.unsetMark(name, { extendEmptyMarkRange: true });
  return chain.run();
}

/** Raccourci clavier de la commande */
const ClearFormattingShortcut = Extension.create({
  name: 'clearFormatting',
  addKeyboardShortcuts() {
    return { 'Mod-\\': () => clearFormatting(this.editor) };
  },
});

export default defineNatheoEditorModule({
  id: 'clearFormatting',
  position: 65,
  extensions: () => [ClearFormattingShortcut],
  commands: [
    {
      id: 'clearFormatting',
      label: t('admin_ui.natheo_editor.command.clear_formatting'),
      icon: 'eraser',
      shortcut: 'Mod-\\',
      group: 'inline',
      run: (editor) => clearFormatting(editor),
      isEnabled: hasFormatting,
    },
  ],
});
