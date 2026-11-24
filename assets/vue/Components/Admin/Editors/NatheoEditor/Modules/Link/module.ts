/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module link de Natheo-Editor : liens vers une adresse autorisée (web, e-mail, téléphone, site, page P#id)
 */
import { Extension } from '@tiptap/core';
import Link from '@tiptap/extension-link';
import { isAllowedLink } from '@/vue/Composables/Admin/Editor/NatheoEditor/natheoEditorUrls';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';

/**
 * Fin du texte d'un lien ouvert en tête de src, crochets imbriqués compris
 * @param src
 * @return position du crochet fermant, -1 sans fermeture
 */
function closingBracket(src: string): number {
  let depth = 0;
  for (let index = 0; index < src.length; index++) {
    const char = src[index];
    if (char === '\\') index++;
    else if (char === '[') depth++;
    else if (char === ']' && --depth === 0) return index;
    else if (char === '\n' && src[index + 1] === '\n') return -1;
  }
  return -1;
}

/** Destination et titre facultatif : (url "titre") */
const DESTINATION = /^\(\s*(<[^<>\n]*>|[^\s()]*(?:\([^\s()]*\)[^\s()]*)*)(?:\s+("[^"]*"|'[^']*'|\([^()]*\)))?\s*\)/;

/** Lien dont le texte contient des crochets sur deux niveaux ([[mot-clé]]), refusé par le lecteur et lu par CommonMark */
const NestedLink = Extension.create({
  name: 'nestedLink',
  markdownTokenizer: {
    name: 'link',
    level: 'inline',
    start: (src: string) => src.search(/\[[^\]\n]*\[\[/),
    tokenize: (src, _tokens, lexer) => {
      if (!src.startsWith('[')) return undefined;
      const end = closingBracket(src);
      const text = end > 0 ? src.slice(1, end) : '';
      if (!text.includes('[[')) return undefined;
      const destination = DESTINATION.exec(src.slice(end + 1));
      if (destination === null) return undefined;
      const href = destination[1].replace(/^<|>$/g, '');
      const title = destination[2]?.slice(1, -1) ?? null;
      return {
        type: 'link',
        raw: src.slice(0, end + 1) + destination[0],
        href,
        title,
        text,
        tokens: lexer.inlineTokens(text),
      };
    },
  },
});

export default defineNatheoEditorModule({
  id: 'link',
  position: 100,
  extensions: () => [
    NestedLink,
    Link.configure({
      openOnClick: false,
      autolink: false,
      HTMLAttributes: { target: null, rel: null },
      isAllowedUri: (url) => isAllowedLink(url),
    }),
  ],
  commands: [
    {
      id: 'link',
      label: t('admin_ui.natheo_editor.command.link'),
      hint: t('admin_ui.natheo_editor.hint.link'),
      icon: 'link',
      shortcut: 'Mod-k',
      group: 'insert',
      isActive: (editor) => editor.isActive('link'),
    },
  ],
});
