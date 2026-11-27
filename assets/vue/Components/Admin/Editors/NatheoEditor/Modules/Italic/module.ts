/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module italic de Natheo-Editor : italique
 */
import Italic from '@tiptap/extension-italic';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';

export default defineNatheoEditorModule({
  id: 'italic',
  position: 40,
  extensions: () => [Italic],
  commands: [
    {
      id: 'italic',
      label: t('admin_ui.natheo_editor.command.italic'),
      icon: 'italic',
      shortcut: 'Mod-i',
      group: 'inline',
      run: (editor) => editor.chain().focus().toggleItalic().run(),
      isActive: (editor) => editor.isActive('italic'),
    },
  ],
});
