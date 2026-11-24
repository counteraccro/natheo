/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module blockquote de Natheo-Editor : citation
 */
import Blockquote from '@tiptap/extension-blockquote';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';

export default defineNatheoEditorModule({
  id: 'blockquote',
  position: 80,
  extensions: () => [Blockquote],
  commands: [
    {
      id: 'blockquote',
      label: t('admin_ui.natheo_editor.command.blockquote'),
      hint: t('admin_ui.natheo_editor.hint.blockquote'),
      markdown: '>',
      icon: 'quote',
      shortcut: 'Mod-Shift-b',
      group: 'block',
      run: (editor) => editor.chain().focus().toggleBlockquote().run(),
      isActive: (editor) => editor.isActive('blockquote'),
    },
  ],
});
