/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module codeBlock de Natheo-Editor : bloc de code avec sa langue
 */
import CodeBlock from '@tiptap/extension-code-block';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';

export default defineNatheoEditorModule({
  id: 'codeBlock',
  position: 90,
  extensions: () => [CodeBlock],
  commands: [
    {
      id: 'codeBlock',
      label: t('admin_ui.natheo_editor.command.code_block'),
      hint: t('admin_ui.natheo_editor.hint.code_block'),
      markdown: '```',
      icon: 'file-code',
      shortcut: 'Mod-Alt-c',
      group: 'block',
      run: (editor) => editor.chain().focus().toggleCodeBlock().run(),
      isActive: (editor) => editor.isActive('codeBlock'),
    },
  ],
});
