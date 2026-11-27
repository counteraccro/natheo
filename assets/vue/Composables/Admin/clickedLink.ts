/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lien suivi par un clic dans la page courante : ni nouvel onglet, ni téléchargement, ni ancre de la page
 */

/**
 * Lien du clic, null s'il est déjà pris en charge ou s'il ne quitte pas la page
 * @param event
 */
export function clickedLink(event: MouseEvent): HTMLAnchorElement | null {
  if (
    event.defaultPrevented ||
    event.button !== 0 ||
    event.metaKey ||
    event.ctrlKey ||
    event.shiftKey ||
    event.altKey
  ) {
    return null;
  }
  const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
  if (
    !(link instanceof HTMLAnchorElement) ||
    (link.target && link.target !== '_self') ||
    link.hasAttribute('download')
  ) {
    return null;
  }
  const url = new URL(link.href, window.location.href);
  const samePage = url.origin === window.location.origin && url.pathname === window.location.pathname;
  return samePage && url.hash !== '' ? null : link;
}
