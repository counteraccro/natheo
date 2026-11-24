/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module strike de Natheo-Editor : barré
 */
import Strike from '@tiptap/extension-strike';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';

export default defineNatheoEditorModule({
  id: 'strike',
  position: 50,
  extensions: () => [Strike],
  commands: [
    {
      id: 'strike',
      label: t('admin_ui.natheo_editor.command.strike'),
      icon: 'strike',
      shortcut: 'Mod-Shift-s',
      group: 'inline',
      run: (editor) => editor.chain().focus().toggleStrike().run(),
      isActive: (editor) => editor.isActive('strike'),
    },
  ],
});
