/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Envoi et lecture des messages entre un écran du back-office et l'aperçu au rendu du thème du site
 */
import type { PreviewMessage, PreviewSource } from '@/ts/Front/Preview.type';

const SOURCE: PreviewSource = 'natheo-preview';

/** Message sans sa marque, réparti sur chaque type de message */
type WithoutSource<M> = M extends PreviewMessage ? Omit<M, 'source'> : never;
type MessageBody = WithoutSource<PreviewMessage>;

/**
 * Envoie un message à la fenêtre `target`, limitée à la même origine
 */
export function postPreviewMessage(target: Window | null | undefined, message: MessageBody): void {
  target?.postMessage({ source: SOURCE, ...message }, window.location.origin);
}

/**
 * Message de l'aperçu venu de la fenêtre `from`, de même origine ; null pour tout autre message
 */
export function readPreviewMessage<T = unknown>(
  event: MessageEvent,
  from: Window | null | undefined
): PreviewMessage<T> | null {
  if (event.origin !== window.location.origin || from === null || from === undefined || event.source !== from) {
    return null;
  }
  const data = event.data as Partial<PreviewMessage<T>> | null;
  return data !== null && typeof data === 'object' && data.source === SOURCE ? (data as PreviewMessage<T>) : null;
}
