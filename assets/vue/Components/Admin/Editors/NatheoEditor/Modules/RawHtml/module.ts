/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module rawHtml de Natheo-Editor : HTML brut et attributs {...} sans équivalent visuel, affichés en code non
 * modifiable et rendus à l'identique
 */
import { Node } from '@tiptap/core';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';

/** Balise ou commentaire HTML en tête du texte */
const INLINE_HTML = /^(?:<!--[\s\S]*?-->|<\/?[A-Za-z][\w-]*(?:\s+[^<>]*?)?\s*\/?>)/;

/** Attributs de league/commonmark en tête du texte : {.classe #id cle="valeur"} */
const ATTRIBUTE = String.raw`(?:[.#][\w-]+|[\w-]+=(?:"[^"\n]*"|'[^'\n]*'|[^\s}"']+))`;
const ATTRIBUTES = new RegExp(String.raw`^\{\s*${ATTRIBUTE}(?:\s+${ATTRIBUTE})*\s*\}`);

/** Bloc HTML, jamais créé par un collage (aucune règle de lecture du HTML) */
export const RawHtmlBlock = Node.create({
  name: 'rawHtml',
  group: 'block',
  atom: true,
  selectable: true,

  addAttributes() {
    return { html: { default: '' } };
  },

  renderHTML({ node }) {
    return ['pre', { class: 'natheo-editor-raw', 'data-raw': 'block' }, node.attrs.html];
  },

  markdownTokenName: 'html',

  parseMarkdown: (token, helpers) =>
    token.block ? helpers.createNode('rawHtml', { html: (token.raw ?? '').replace(/\n+$/, '') }) : [],

  renderMarkdown: (node) => node.attrs?.html ?? '',
});

/**
 * Élément gardé dans le texte (balise HTML ou attributs), lu par son propre jeton
 * @param name
 * @param pattern
 * @param start
 */
const inlineRaw = (name: string, pattern: RegExp, start: string) =>
  Node.create({
    name,
    group: 'inline',
    inline: true,
    atom: true,
    selectable: true,

    addAttributes() {
      return { raw: { default: '' } };
    },

    renderHTML({ node }) {
      return ['code', { class: 'natheo-editor-raw', 'data-raw': name }, node.attrs.raw];
    },

    renderText({ node }) {
      return node.attrs.raw;
    },

    markdownTokenizer: {
      name,
      level: 'inline',
      start: (src: string) => src.indexOf(start),
      tokenize: (src: string) => {
        const match = pattern.exec(src);
        return match ? { type: name, raw: match[0], text: match[0] } : undefined;
      },
    },

    parseMarkdown: (token, helpers) => helpers.createNode(name, { raw: token.raw }),

    renderMarkdown: (node) => node.attrs?.raw ?? '',
  });

export const RawHtmlInline = inlineRaw('rawHtmlInline', INLINE_HTML, '<');
export const RawAttributes = inlineRaw('rawAttributes', ATTRIBUTES, '{');

export default defineNatheoEditorModule({
  id: 'rawHtml',
  position: 150,
  always: true,
  extensions: () => [RawHtmlBlock, RawHtmlInline, RawAttributes],
});
