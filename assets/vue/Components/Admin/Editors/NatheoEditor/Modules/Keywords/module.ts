/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module keywords de Natheo-Editor : mots-clés [[x]] gardés tels quels, insérés depuis la liste donnée par le champ
 */
import { Node, nodeInputRule } from '@tiptap/core';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';

/** Mot-clé en tête du texte : [[user.login]] */
const KEYWORD = /^\[\[([\w.-]+)\]\]/;

/** Mot-clé atomique, rendu en pastille */
export const Keyword = Node.create({
  name: 'keyword',
  group: 'inline',
  inline: true,
  atom: true,
  selectable: true,

  addAttributes() {
    return { name: { default: '' } };
  },

  parseHTML() {
    return [{ tag: 'span[data-keyword]', getAttrs: (element) => ({ name: element.getAttribute('data-keyword') }) }];
  },

  renderHTML({ node }) {
    return ['span', { 'data-keyword': node.attrs.name, class: 'natheo-editor-keyword' }, `[[${node.attrs.name}]]`];
  },

  renderText({ node }) {
    return `[[${node.attrs.name}]]`;
  },

  addInputRules() {
    // Sans groupe capturant : la règle remplace tout le texte trouvé, crochets compris
    return [
      nodeInputRule({
        find: /\[\[[\w.-]+\]\]$/,
        type: this.type,
        getAttributes: (match) => ({ name: match[0].slice(2, -2) }),
      }),
    ];
  },

  markdownTokenizer: {
    name: 'keyword',
    level: 'inline',
    start: (src: string) => src.indexOf('[['),
    tokenize: (src: string) => {
      const match = KEYWORD.exec(src);
      return match ? { type: 'keyword', raw: match[0], text: match[1] } : undefined;
    },
  },

  parseMarkdown: (token, helpers) => helpers.createNode('keyword', { name: token.text }),

  renderMarkdown: (node) => `[[${node.attrs?.name ?? ''}]]`,
});

export default defineNatheoEditorModule({
  id: 'keywords',
  position: 140,
  // Toujours lu pour que [[x]] ne soit jamais échappé ; les commandes n'existent qu'avec des mots-clés
  always: true,
  extensions: () => [Keyword],
  commands: ({ props }) =>
    props.keywords.map((keyword) => {
      const name = keyword.keyword.replace(/^\[\[|\]\]$/g, '');
      return {
        id: `keyword:${name}`,
        label: keyword.label,
        hint: keyword.keyword,
        translated: true,
        icon: 'tag',
        group: 'keyword',
        run: (editor) => editor.chain().focus().insertContent({ type: 'keyword', attrs: { name } }).run(),
      };
    }),
});
