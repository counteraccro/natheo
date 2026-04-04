/**
 * mediaModule.ts
 *
 * Module "Médiathèque" pour la toolbar du MarkdownEditor.
 * Utilise le pattern CustomEvent de Nathéo :
 *   - le module dispatch `natheo:open-media` sur window
 *   - le composant parent (ex. EditPage.vue) écoute l'event et ouvre MediaPickerModal
 *   - la modale appelle onSelect() avec le média choisi
 *   - le module insère le markdown correspondant dans l'éditeur
 *
 * @author Gourdon Aymeric
 * @version 1.0
 */

import type { EditorModule, EditorApi } from '@/ts/MarkdownEditor/MarkdownEditor.types';

// ─── Types ────────────────────────────────────────────────────────────────────

export interface MediaFile {
  name: string;
  url: string;
  type: 'image' | 'file';
  alt?: string;
}

export interface NatheoMediaEvent extends CustomEvent {
  detail: {
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

//const IMAGE_EXTENSIONS = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg', '.avif'];

/*function isImage(filename: string): boolean {
  const ext = filename.match(/\.[a-z0-9]+$/i)?.[0]?.toLowerCase() ?? '';
  return IMAGE_EXTENSIONS.includes(ext);
}*/

function buildMarkdown(media: MediaFile): string {
  if (media.type === 'image') {
    const alt = media.alt ?? media.name;
    return `![${alt}](${media.url})`;
  }
  return `[${media.name}](${media.url})`;
}

// ─── Module ───────────────────────────────────────────────────────────────────

export const MediaModule: EditorModule = {
  name: 'media',
  label: 'Médiathèque',

  icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
    <circle cx="8.5" cy="8.5" r="1.5"/>
    <polyline points="21 15 16 10 5 21"/>
  </svg>`,

  action(api: EditorApi): void {
    const event = new CustomEvent('natheo:open-media', {
      detail: {
        onSelect: (media: MediaFile) => {
          api.insertBlock(buildMarkdown(media));
        },
      },
    }) as NatheoMediaEvent;

    window.dispatchEvent(event);
  },
};
