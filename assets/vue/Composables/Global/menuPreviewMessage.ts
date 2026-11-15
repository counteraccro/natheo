/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Envoi et lecture des messages entre l'éditeur de menu et l'aperçu du menu au design du thème
 */
import type { MenuPreviewMessage, MenuPreviewSource } from '@/ts/Front/MenuPreview.type';

const SOURCE: MenuPreviewSource = 'natheo-menu-preview';

/** Message sans sa marque, réparti sur chaque type de message */
type WithoutSource<M> = M extends MenuPreviewMessage ? Omit<M, 'source'> : never;
type MessageBody = WithoutSource<MenuPreviewMessage>;

/**
 * Envoie un message à la fenêtre `target`, limitée à la même origine
 */
export function postMenuPreviewMessage(target: Window | null | undefined, message: MessageBody): void {
  target?.postMessage({ source: SOURCE, ...message }, window.location.origin);
}

/**
 * Message de l'aperçu venu de la fenêtre `from`, de même origine ; null pour tout autre message
 */
export function readMenuPreviewMessage(
  event: MessageEvent,
  from: Window | null | undefined
): MenuPreviewMessage | null {
  if (event.origin !== window.location.origin || from === null || from === undefined || event.source !== from) {
    return null;
  }
  const data = event.data as Partial<MenuPreviewMessage> | null;
  return data !== null && typeof data === 'object' && data.source === SOURCE ? (data as MenuPreviewMessage) : null;
}
