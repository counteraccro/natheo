/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Modules intégrés de la toolbar markdown
 */

import type { EditorModule, ToolbarDefinition } from '@/ts/MarkdownEditor/MarkdownEditor.types';

// ─── Helper icônes ────────────────────────────────────────────────────────────

const icon = (path: string): string =>
  `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${path}</svg>`;

const ICONS: Record<string, string> = {
  bold: icon('<path d="M6 4h8a4 4 0 010 8H6z"/><path d="M6 12h9a4 4 0 010 8H6z"/>'),
  italic: icon(
    '<line x1="19" y1="4" x2="10" y2="4"/><line x1="14" y1="20" x2="5" y2="20"/><line x1="15" y1="4" x2="9" y2="20"/>'
  ),
  strike: icon(
    '<line x1="18" y1="12" x2="6" y2="12"/><path d="M16 6C16 6 14 4 12 4C9.79 4 8 5.79 8 8C8 10.21 10 11 12 11"/><path d="M8 18C8 18 10 20 12 20C14.21 20 16 18.21 16 16C16 13.79 14 13 12 13"/>'
  ),
  h1: icon('<path d="M4 12h8M4 18V6M12 18V6M17 12l5-4v12"/>'),
  h2: icon('<path d="M4 12h8M4 18V6M12 18V6M21 18H15l4.5-5.35a2.5 2.5 0 10-4.35-2.15"/>'),
  h3: icon(
    '<path d="M4 12h8M4 18V6M12 18V6M17.5 10.5c1.7-1 3.5 0 3.5 1.5a2 2 0 01-2 2"/><path d="M17.5 13.5c2 1.5 3.5 2.5 3.5 4A2.5 2.5 0 0118.5 20H17"/>'
  ),
  quote: icon(
    '<path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/>'
  ),
  code: icon('<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>'),
  codeblock: icon(
    '<rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>'
  ),
  link: icon(
    '<path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>'
  ),
  ul: icon(
    '<line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4" cy="6" r="1" fill="currentColor"/><circle cx="4" cy="12" r="1" fill="currentColor"/><circle cx="4" cy="18" r="1" fill="currentColor"/>'
  ),
  ol: icon(
    '<line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><path d="M4 6h1v4M4 10H6M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/>'
  ),
  hr: icon(
    '<line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>'
  ),
  table: icon(
    '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="12" y1="3" x2="12" y2="21"/>'
  ),
};

// ─── Modules intégrés ─────────────────────────────────────────────────────────

export const BUILTIN_MODULES: Record<string, EditorModule> = {
  bold: {
    name: 'bold',
    label: 'Gras (Ctrl+B)',
    icon: ICONS.bold,
    shortcut: { key: 'b', ctrl: true },
    action: ({ wrapSelection }) => wrapSelection('**', '**', 'texte en gras'),
  },
  italic: {
    name: 'italic',
    label: 'Italique (Ctrl+I)',
    icon: ICONS.italic,
    shortcut: { key: 'i', ctrl: true },
    action: ({ wrapSelection }) => wrapSelection('*', '*', 'texte en italique'),
  },
  strikethrough: {
    name: 'strikethrough',
    label: 'Barré',
    icon: ICONS.strike,
    action: ({ wrapSelection }) => wrapSelection('~~', '~~', 'texte barré'),
  },
  h1: {
    name: 'h1',
    label: 'Titre 1',
    icon: ICONS.h1,
    action: ({ insertLinePrefix }) => insertLinePrefix('# '),
  },
  h2: {
    name: 'h2',
    label: 'Titre 2',
    icon: ICONS.h2,
    action: ({ insertLinePrefix }) => insertLinePrefix('## '),
  },
  h3: {
    name: 'h3',
    label: 'Titre 3',
    icon: ICONS.h3,
    action: ({ insertLinePrefix }) => insertLinePrefix('### '),
  },
  blockquote: {
    name: 'blockquote',
    label: 'Citation',
    icon: ICONS.quote,
    action: ({ insertLinePrefix }) => insertLinePrefix('> '),
  },
  code: {
    name: 'code',
    label: 'Code inline',
    icon: ICONS.code,
    action: ({ wrapSelection }) => wrapSelection('`', '`', 'code'),
  },
  codeblock: {
    name: 'codeblock',
    label: 'Bloc de code',
    icon: ICONS.codeblock,
    action: ({ insertBlock }) => insertBlock('```\n\n```'),
  },
  ul: {
    name: 'ul',
    label: 'Liste à puces',
    icon: ICONS.ul,
    action: ({ insertLinePrefix }) => insertLinePrefix('- '),
  },
  ol: {
    name: 'ol',
    label: 'Liste numérotée',
    icon: ICONS.ol,
    action: ({ insertLinePrefix }) => insertLinePrefix('1. '),
  },
  hr: {
    name: 'hr',
    label: 'Séparateur',
    icon: ICONS.hr,
    action: ({ insertBlock }) => insertBlock('---'),
  },
  table: {
    name: 'table',
    label: 'Tableau',
    icon: ICONS.table,
    action: ({ insertBlock }) =>
      insertBlock(
        '| Colonne 1 | Colonne 2 | Colonne 3 |\n' +
          '|-----------|-----------|----------|\n' +
          '| Cellule   | Cellule   | Cellule  |'
      ),
  },
  link: {
    name: 'link',
    label: 'Lien (Ctrl+K)',
    icon: ICONS.link,
    shortcut: { key: 'k', ctrl: true },
    action: ({ wrapSelection, getSelection }) => {
      const { text } = getSelection();
      wrapSelection('[', '](url)', text || 'Texte du lien');
    },
  },
};

// ─── Toolbar par défaut ───────────────────────────────────────────────────────

export const DEFAULT_TOOLBAR: ToolbarDefinition = [
  ['h1', 'h2', 'h3'],
  ['bold', 'italic', 'strikethrough'],
  ['blockquote', 'code', 'codeblock'],
  ['ul', 'ol'],
  ['link', 'table', 'hr'],
];
