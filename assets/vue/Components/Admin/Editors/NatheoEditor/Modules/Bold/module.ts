/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module bold de Natheo-Editor : gras
 */
import Bold from '@tiptap/extension-bold';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';

export default defineNatheoEditorModule({
  id: 'bold',
  position: 30,
  extensions: () => [Bold],
  commands: [
    {
      id: 'bold',
      label: t('admin_ui.natheo_editor.command.bold'),
      icon: 'bold',
      shortcut: 'Mod-b',
      group: 'inline',
      run: (editor) => editor.chain().focus().toggleBold().run(),
      isActive: (editor) => editor.isActive('bold'),
    },
  ],
});
