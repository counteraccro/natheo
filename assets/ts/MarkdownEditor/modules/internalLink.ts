/**
 * @author Gourdon Aymeric
 * @version 2.0
 *
 * Module "lien interne" pour la toolbar du MarkdownEditor.
 */

import type { EditorModule, EditorApi } from '@/ts/MarkdownEditor/MarkdownEditor.types';

// ─── Types ────────────────────────────────────────────────────────────────────

export interface InternalPage {
  id: number;
  title: string;
}

export interface NatheoInternalLinkEvent extends CustomEvent {
  detail: {
    onSelect: (page: InternalPage) => void;
  };
}

// ─── Déclaration de l'événement sur window (pour TypeScript) ──────────────────

declare global {
  interface WindowEventMap {
    'natheo:open-internal-link': NatheoInternalLinkEvent;
  }
}

// ─── Module ───────────────────────────────────────────────────────────────────

export const InternalLinkModule: EditorModule = {
  name: 'internal-link',
  label: 'Lien interne',
  icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z"/>
    <polyline points="13 2 13 9 20 9"/>
    <path d="M9 14h6M9 17h3"/>
  </svg>`,

  action(api: EditorApi): void {
    const event: NatheoInternalLinkEvent = new CustomEvent('natheo:open-internal-link', {
      detail: {
        onSelect: (page: InternalPage) => {
          // Récupère le texte sélectionné dans l'éditeur
          // si rien n'est sélectionné, on utilise le titre de la page
          const { text } = api.getSelection();
          api.wrapSelection('[', `](P#${page.id})`, text || page.title);
        },
      },
    }) as NatheoInternalLinkEvent;

    window.dispatchEvent(event);
  },
};
