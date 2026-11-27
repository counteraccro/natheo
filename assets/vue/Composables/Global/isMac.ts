/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Système d'Apple : raccourcis écrits avec ⌘ et touche Cmd au lieu de Ctrl
 */
export function isMac(): boolean {
  return typeof navigator !== 'undefined' && /Mac|iPhone|iPad/.test(navigator.userAgent);
}
