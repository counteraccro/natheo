/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module document de Natheo-Editor : document, paragraphe, texte et saut de ligne forcé (\ en fin de ligne) ; un saut
 * de ligne simple, une adresse nue et une entité HTML sont lus comme le serveur les rend
 */
import { Extension, type JSONContent } from '@tiptap/core';
import Document from '@tiptap/extension-document';
import Paragraph from '@tiptap/extension-paragraph';
import Text from '@tiptap/extension-text';
import HardBreak from '@tiptap/extension-hard-break';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';

/** Adresse web ou e-mail nue, qu'un lecteur GFM changerait en lien */
const BARE_URL = /^(?:(?:https?:\/\/|www\.)[^\s<]+|[\w.+-]+@[\w-]+(?:\.[\w-]+)+)/;
const BARE_URL_START = /(?:https?:\/\/|www\.)|[\w.+-]+@[\w-]+\./;

/**
 * Sauts de ligne simples d'un contenu changés en espaces, hors code
 * @param nodes
 */
function joinSoftBreaks(nodes: JSONContent[]): JSONContent[] {
  return nodes.map((node) =>
    node.type === 'text' && node.text?.includes('\n') && !node.marks?.some((mark) => mark.type === 'code')
      ? { ...node, text: node.text.replace(/[ \t]*\n[ \t]*/g, ' ') }
      : node
  );
}

/**
 * Le serveur rend un saut de ligne simple dans le même paragraphe ; une image seule reste dans son paragraphe, les
 * images étant dans le texte
 */
const SoftBreakParagraph = Paragraph.extend({
  parseMarkdown(token, helpers) {
    const parsed = this.parent?.(token, helpers);
    if (parsed && !Array.isArray(parsed) && 'type' in parsed && parsed.type === 'paragraph') {
      return { ...parsed, content: joinSoftBreaks(parsed.content ?? []) };
    }
    const content = parsed === undefined ? [] : Array.isArray(parsed) ? parsed : 'type' in parsed ? [parsed] : [];
    return helpers.createNode('paragraph', undefined, content);
  },
  // Paragraphe vide écrit &nbsp;, sinon il disparaîtrait du site
  renderMarkdown(node, helpers) {
    return node.content?.length ? helpers.renderChildren(node.content) : '&nbsp;';
  },
});

/** Paragraphes vides en début et en fin de texte, sans effet sur le site */
const EDGE_EMPTY_PARAGRAPHS = /^(?:&nbsp;\s*)+|(?:\s*&nbsp;)+\s*$/g;

/** Le serveur n'a pas d'extension autolink : une adresse nue reste du texte */
const BareUrl = Extension.create({
  name: 'bareUrl',
  markdownTokenizer: {
    name: 'bareUrl',
    level: 'inline',
    start: (src: string) => src.search(BARE_URL_START),
    tokenize: (src: string) => {
      const match = BARE_URL.exec(src);
      return match ? { type: 'text', raw: match[0], text: match[0] } : undefined;
    },
  },
});

/** Entité HTML (&nbsp;, &#169;) : lue comme son caractère, comme le fait le serveur */
const HTML_ENTITY = /^&(?:#\d{1,7}|#x[\da-f]{1,6}|[a-z][a-z\d]{1,31});/i;

const HtmlEntity = Extension.create({
  name: 'htmlEntity',
  markdownTokenizer: {
    name: 'htmlEntity',
    level: 'inline',
    start: (src: string) => src.search(/&(?:#|[a-z])/i),
    tokenize: (src: string) => {
      const match = HTML_ENTITY.exec(src);
      if (match === null) return undefined;
      const text = new DOMParser().parseFromString(match[0], 'text/html').body.textContent ?? match[0];
      return { type: 'text', raw: match[0], text };
    },
  },
});

export default defineNatheoEditorModule({
  id: 'document',
  position: 0,
  always: true,
  transformMarkdown: (markdown) => markdown.replace(EDGE_EMPTY_PARAGRAPHS, ''),
  extensions: () => [
    Document,
    SoftBreakParagraph,
    Text,
    HardBreak.extend({ renderMarkdown: () => '\\\n' }),
    BareUrl,
    HtmlEntity,
  ],
});
