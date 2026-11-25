/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module list de Natheo-Editor : listes à puces (-) et numérotées, serrées ou espacées comme dans le texte reçu
 */
import { BulletList, ListItem, ListKit, OrderedList } from '@tiptap/extension-list';
import type { JSONContent, MarkdownToken } from '@tiptap/core';
import { defineNatheoEditorModule } from '@/vue/Composables/Admin/Editor/NatheoEditor/defineNatheoEditorModule';
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';

/** Liste espacée (éléments séparés par une ligne vide), rendue avec des paragraphes */
const loose = {
  default: false,
  rendered: false,
  keepOnSplit: true,
};

/**
 * Liste lue, marquée espacée comme le jeton
 * @param parsed
 * @param token
 */
const withLoose = (parsed: unknown, token: MarkdownToken): unknown =>
  parsed && !Array.isArray(parsed) && token.loose
    ? { ...(parsed as JSONContent), attrs: { ...(parsed as JSONContent).attrs, loose: true } }
    : parsed;

/**
 * Éléments d'une liste séparés par une ligne vide quand elle est espacée
 * @param node
 * @param renderChildren
 */
const renderItems = (
  node: JSONContent,
  renderChildren: (nodes: JSONContent[], separator?: string) => string
): string => (node.content ? renderChildren(node.content, node.attrs?.loose ? '\n\n' : '\n') : '');

/**
 * Puces écrites avec -, sauf une liste qui en suit une autre : deux listes voisines de même puce n'en feraient
 * qu'une à la relecture, la puce alterne donc entre * et + selon la position
 */
const NatheoBulletList = BulletList.extend({
  addAttributes() {
    return { ...this.parent?.(), loose };
  },
  parseMarkdown(token, helpers) {
    return withLoose(this.parent?.(token, helpers), token) as JSONContent;
  },
  renderMarkdown(node, helpers, context) {
    const markdown = renderItems(node, helpers.renderChildren);
    if (context.previousNode?.type !== 'bulletList') return markdown;
    return markdown.replace(/^- /gm, context.index % 2 === 0 ? '* ' : '+ ');
  },
});

/** Liste numérotée lue par marked (un changement de délimiteur commence une liste) ; ) après une autre liste */
const NatheoOrderedList = OrderedList.extend({
  addAttributes() {
    return { ...this.parent?.(), loose };
  },
  markdownTokenizer: { name: 'orderedList', level: 'block', start: () => -1, tokenize: () => undefined },
  parseMarkdown(token, helpers) {
    return withLoose(this.parent?.(token, helpers), token) as JSONContent;
  },
  renderMarkdown(node, helpers, context) {
    const markdown = renderItems(node, helpers.renderChildren);
    if (context.previousNode?.type !== 'orderedList') return markdown;
    return markdown.replace(/^(\d+)\. /gm, '$1) ');
  },
});

/** Élément d'une liste espacée : ligne vide après son premier bloc, qui suffit à garder la liste espacée */
const NatheoListItem = ListItem.extend({
  renderMarkdown(node, helpers, context) {
    const markdown = this.parent?.(node, helpers, context) ?? '';
    if (!context.meta?.parentAttrs?.loose || (node.content?.length ?? 0) < 2) return markdown;
    const first = helpers.renderChildren([node.content![0]]);
    const end = markdown.indexOf(first) + first.length;
    return markdown[end] === '\n' && markdown[end + 1] !== '\n'
      ? `${markdown.slice(0, end)}\n${markdown.slice(end)}`
      : markdown;
  },
});

export default defineNatheoEditorModule({
  id: 'list',
  position: 70,
  extensions: () => [
    ListKit.configure({ bulletList: false, orderedList: false, listItem: false, taskList: false, taskItem: false }),
    NatheoBulletList,
    NatheoOrderedList,
    NatheoListItem,
  ],
  commands: [
    {
      id: 'bulletList',
      label: t('admin_ui.natheo_editor.command.bullet_list'),
      hint: t('admin_ui.natheo_editor.hint.bullet_list'),
      markdown: '-',
      icon: 'list',
      shortcut: 'Mod-Shift-8',
      group: 'list',
      run: (editor) => editor.chain().focus().toggleBulletList().run(),
      isActive: (editor) => editor.isActive('bulletList'),
    },
    {
      id: 'orderedList',
      label: t('admin_ui.natheo_editor.command.ordered_list'),
      hint: t('admin_ui.natheo_editor.hint.ordered_list'),
      markdown: '1.',
      icon: 'list-ordered',
      shortcut: 'Mod-Shift-7',
      group: 'list',
      run: (editor) => editor.chain().focus().toggleOrderedList().run(),
      isActive: (editor) => editor.isActive('orderedList'),
    },
  ],
});
