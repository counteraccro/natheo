/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module history de Natheo-Editor : annuler, rétablir
 */
import { UndoRedo } from '@tiptap/extensions';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';

export default defineNatheoEditorModule({
  id: 'history',
  position: 10,
  extensions: () => [UndoRedo],
  commands: [
    {
      id: 'undo',
      label: t('admin_ui.natheo_editor.command.undo'),
      icon: 'undo',
      shortcut: 'Mod-z',
      group: 'history',
      run: (editor) => editor.chain().focus().undo().run(),
      isEnabled: (editor) => editor.can().undo(),
    },
    {
      id: 'redo',
      label: t('admin_ui.natheo_editor.command.redo'),
      icon: 'redo',
      shortcut: 'Mod-Shift-z',
      group: 'history',
      run: (editor) => editor.chain().focus().redo().run(),
      isEnabled: (editor) => editor.can().redo(),
    },
  ],
});
