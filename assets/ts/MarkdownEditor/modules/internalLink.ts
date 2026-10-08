/**
 * @author Gourdon Aymeric
 * @version 3.0
 *
 * Module "lien interne" pour la toolbar du MarkdownEditor.
 * Dispatch `natheo:open-internal-link` sur window ; seule la modale de l'éditeur émetteur y répond
 */

import type { EditorModule, EditorApi } from '@/ts/MarkdownEditor/MarkdownEditor.type';
import { escapeLinkText } from '@/ts/MarkdownEditor/markdownEditorCore';

// ─── Types ────────────────────────────────────────────────────────────────────

export interface InternalPage {
  id: number;
  title: string;
}

export interface NatheoInternalLinkEvent extends CustomEvent {
  detail: {
    editorId: string;
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
  translateKey: 'btnLinkInterne',
  icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <path d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z"/>
    <polyline points="13 2 13 9 20 9"/>
    <path d="M9 14h6M9 17h3"/>
  </svg>`,

  action(api: EditorApi): void {
    const event = new CustomEvent('natheo:open-internal-link', {
      detail: {
        editorId: api.editorId,
        onSelect: (page: InternalPage) => {
          // Le texte sélectionné est conservé, sinon le titre de la page sert de libellé
          api.wrapSelection('[', `](P#${page.id})`, escapeLinkText(page.title));
        },
      },
    }) as NatheoInternalLinkEvent;

    window.dispatchEvent(event);
  },
};
