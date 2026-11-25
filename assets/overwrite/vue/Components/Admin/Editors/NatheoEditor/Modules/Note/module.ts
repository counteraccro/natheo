/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Exemple de surcharge : module « Note » de Natheo-Editor, encadré enregistré en citation qui commence par **Note**
 */
import { Node } from '@tiptap/core';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';

/** Première ligne d'une note, puis les lignes de la citation */
const NOTE = /^> \*\*Note\*\*[ \t]*(?:\n|$)(?:>[^\n]*(?:\n|$))*/;

/** Encadré dont le contenu est fait de blocs (paragraphes, listes…) */
export const NoteBlock = Node.create({
  name: 'note',
  group: 'block',
  content: 'block+',
  defining: true,

  parseHTML() {
    return [{ tag: 'aside[data-note]' }];
  },

  renderHTML() {
    return [
      'aside',
      {
        'data-note': '',
        class:
          "rounded-md border-l-4 border-primary bg-surface px-4 py-3 before:mb-1 before:block before:text-sm before:font-bold before:text-primary before:content-['Note']",
      },
      0,
    ];
  },

  markdownTokenizer: {
    name: 'note',
    level: 'block',
    start: (src: string) => src.search(/^> \*\*Note\*\*/m),
    tokenize: (src, _tokens, lexer) => {
      const match = NOTE.exec(src);
      if (!match) {
        return undefined;
      }
      const body = match[0]
        .replace(/\n$/, '')
        .split('\n')
        .slice(1)
        .map((line) => line.replace(/^> ?/, ''))
        .join('\n')
        .trim();
      return { type: 'note', raw: match[0], tokens: lexer.blockTokens(body) };
    },
  },

  parseMarkdown: (token, helpers) => {
    const children = (helpers.parseBlockChildren ?? helpers.parseChildren)(token.tokens ?? []);
    return helpers.createNode('note', undefined, children.length > 0 ? children : [helpers.createNode('paragraph')]);
  },

  renderMarkdown: (node, helpers) => {
    const blocks = (node.content ?? []).map((child, index) =>
      (helpers.renderChild?.(child, index) ?? helpers.renderChildren([child]))
        .split('\n')
        .map((line) => (line.trim() === '' ? '>' : `> ${line}`))
        .join('\n')
    );
    return ['> **Note**', ...blocks].join('\n>\n');
  },
});

export default defineNatheoEditorModule({
  id: 'note',
  position: 85,
  requires: 'demo_overwrite',
  domains: ['demo_overwrite'],
  extensions: () => [NoteBlock],
  commands: [
    {
      id: 'note',
      label: t('demo_overwrite.editor.note_label'),
      hint: t('demo_overwrite.editor.note_hint'),
      icon: 'info',
      group: 'block',
      run: (editor) => editor.chain().focus().toggleWrap('note').run(),
      isActive: (editor) => editor.isActive('note'),
    },
  ],
});
