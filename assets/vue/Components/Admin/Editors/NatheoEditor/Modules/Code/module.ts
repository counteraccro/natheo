/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module code de Natheo-Editor : code dans le texte
 */
import Code from '@tiptap/extension-code';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';

export default defineNatheoEditorModule({
  id: 'code',
  position: 60,
  extensions: () => [Code],
  commands: [
    {
      id: 'code',
      label: t('admin_ui.natheo_editor.command.code'),
      icon: 'code',
      shortcut: 'Mod-e',
      group: 'inline',
      run: (editor) => editor.chain().focus().toggleCode().run(),
      isActive: (editor) => editor.isActive('code'),
    },
  ],
});
