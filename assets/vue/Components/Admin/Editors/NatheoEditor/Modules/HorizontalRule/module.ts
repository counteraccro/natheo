/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module horizontalRule de Natheo-Editor : séparateur
 */
import HorizontalRule from '@tiptap/extension-horizontal-rule';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';

export default defineNatheoEditorModule({
  id: 'horizontalRule',
  position: 130,
  extensions: () => [HorizontalRule],
  commands: [
    {
      id: 'horizontalRule',
      label: t('admin_ui.natheo_editor.command.horizontal_rule'),
      hint: t('admin_ui.natheo_editor.hint.horizontal_rule'),
      markdown: '---',
      icon: 'minus',
      group: 'insert',
      run: (editor) => editor.chain().focus().setHorizontalRule().run(),
    },
  ],
});
