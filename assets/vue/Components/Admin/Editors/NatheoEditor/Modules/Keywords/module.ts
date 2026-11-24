/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module keywords de Natheo-Editor : mots-clés [[x]] gardés en texte sans échappement et montrés en pastille, insérés
 * depuis la liste donnée par le champ
 */
import { Extension } from '@tiptap/core';
import { Plugin, PluginKey } from '@tiptap/pm/state';
import { Decoration, DecorationSet } from '@tiptap/pm/view';
import type { Node as ProseMirrorNode } from '@tiptap/pm/model';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';

/** Mot-clé dans le texte : [[user.login]] */
const KEYWORD = /\[\[[\w.-]+\]\]/g;

/** Mot-clé échappé par l'écriture du Markdown, _ compris */
const ESCAPED_KEYWORD = /\\\[\\\[((?:\\_|[\w.-])+)\\\]\\\]/g;

/**
 * Pastilles des mots-clés du document
 * @param doc
 */
function keywordDecorations(doc: ProseMirrorNode): DecorationSet {
  const decorations: Decoration[] = [];
  doc.descendants((node, position) => {
    if (!node.isText || node.marks.some((mark) => mark.type.spec.code)) return;
    for (const match of node.text?.matchAll(KEYWORD) ?? []) {
      const from = position + (match.index ?? 0);
      decorations.push(Decoration.inline(from, from + match[0].length, { class: 'natheo-editor-keyword' }));
    }
  });
  return DecorationSet.create(doc, decorations);
}

/** Mots-clés montrés en pastille, sans changer le texte */
export const Keywords = Extension.create({
  name: 'keywords',

  addProseMirrorPlugins() {
    return [
      new Plugin({
        key: new PluginKey('natheoEditorKeywords'),
        state: {
          init: (_, state) => keywordDecorations(state.doc),
          apply: (transaction, decorations) =>
            transaction.docChanged ? keywordDecorations(transaction.doc) : decorations,
        },
        props: {
          decorations(state) {
            return this.getState(state);
          },
        },
      }),
    ];
  },
});

export default defineNatheoEditorModule({
  id: 'keywords',
  position: 140,
  // Toujours chargé pour que [[x]] ne soit jamais échappé ; les commandes n'existent qu'avec des mots-clés
  always: true,
  extensions: () => [Keywords],
  transformMarkdown: (markdown) =>
    markdown.replace(ESCAPED_KEYWORD, (_, name: string) => `[[${name.replace(/\\_/g, '_')}]]`),
  commands: ({ props }) =>
    props.keywords.map((keyword) => ({
      id: `keyword:${keyword.keyword.replace(/^\[\[|\]\]$/g, '')}`,
      label: keyword.label,
      hint: keyword.keyword,
      translated: true,
      icon: 'tag',
      group: 'keyword',
      run: (editor) => editor.chain().focus().insertContent(keyword.keyword).run(),
    })),
});
