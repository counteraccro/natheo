/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module codeBlock de Natheo-Editor : bloc de code avec sa langue
 */
import CodeBlock from '@tiptap/extension-code-block';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';

/** Clôture plus longue que la plus longue suite d'accents graves du code */
const FencedCodeBlock = CodeBlock.extend({
  renderMarkdown(node, helpers) {
    const code = helpers.renderChildren(node.content ?? []);
    const longest = Math.max(0, ...(code.match(/`+/g) ?? []).map((run: string) => run.length));
    const fence = '`'.repeat(Math.max(3, longest + 1));
    return `${fence}${node.attrs?.language ?? ''}\n${code}\n${fence}`;
  },
});

export default defineNatheoEditorModule({
  id: 'codeBlock',
  position: 90,
  extensions: () => [FencedCodeBlock],
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
