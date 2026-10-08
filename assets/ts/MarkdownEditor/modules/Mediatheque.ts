/**
 * Module "Médiathèque" pour la toolbar du MarkdownEditor.
 * Utilise le pattern CustomEvent de Nathéo :
 *   - le module dispatch `natheo:open-media` sur window avec l'identifiant de l'éditeur
 *   - la modale MediathequeModale du même éditeur écoute l'event et s'ouvre
 *   - la modale appelle onSelect() avec le média choisi
 *   - le module insère le markdown correspondant dans l'éditeur
 *
 * @author Gourdon Aymeric
 * @version 2.0
 */

import type { EditorModule, EditorApi } from '@/ts/MarkdownEditor/MarkdownEditor.type';
import { escapeLinkText, formatLinkUrl } from '@/ts/MarkdownEditor/markdownEditorCore';

// ─── Types ────────────────────────────────────────────────────────────────────

export interface MediaFile {
  name: string;
  url: string;
  type: 'image' | 'file';
  alt?: string;
}

export interface NatheoMediaEvent extends CustomEvent {
  detail: {
    /** Absent quand l'event ne vient pas d'un éditeur (ex. image d'en-tête de page) */
    editorId?: string;
    onSelect: (media: MediaFile) => void;
  };
}

// ─── Déclaration globale pour TypeScript ──────────────────────────────────────

declare global {
  interface WindowEventMap {
    'natheo:open-media': NatheoMediaEvent;
  }
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

function buildMarkdown(media: MediaFile): string {
  const url = formatLinkUrl(media.url);
  if (media.type === 'image') {
    return `![${escapeLinkText(media.alt ?? media.name)}](${url})`;
  }
  return `[${escapeLinkText(media.name)}](${url})`;
}

// ─── Module ───────────────────────────────────────────────────────────────────

export const MediaModule: EditorModule = {
  name: 'media',
  label: 'Médiathèque',
  translateKey: 'btnMediatheque',

  icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
    <circle cx="8.5" cy="8.5" r="1.5"/>
    <polyline points="21 15 16 10 5 21"/>
  </svg>`,

  action(api: EditorApi): void {
    const event = new CustomEvent('natheo:open-media', {
      detail: {
        editorId: api.editorId,
        onSelect: (media: MediaFile) => {
          api.insertBlock(buildMarkdown(media));
        },
      },
    }) as NatheoMediaEvent;

    window.dispatchEvent(event);
  },
};
