/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Rendu Markdown → HTML sécurisé, à utiliser pour tout v-html issu de markdown
 */

import { Marked } from 'marked';
import DOMPurify from 'dompurify';

// Instance dédiée : ne modifie pas l'instance globale de marked
const defaultParser = new Marked({ gfm: true });

/**
 * Nettoie du HTML pour un affichage via v-html
 * @param html
 * @param allowTarget autorise l'attribut target (liens ouvrant un nouvel onglet)
 */
export function sanitizeHtml(html: string, allowTarget: boolean = false): string {
  return DOMPurify.sanitize(html, allowTarget ? { ADD_ATTR: ['target'] } : {});
}

/**
 * Convertit du markdown en HTML nettoyé
 * @param markdown
 * @param parser instance Marked à utiliser (renderer personnalisé)
 * @param allowTarget autorise l'attribut target
 */
export function renderMarkdown(
  markdown: string | null | undefined,
  parser: Marked = defaultParser,
  allowTarget: boolean = false
): string {
  if (!markdown) return '';
  return sanitizeHtml(parser.parse(markdown, { async: false }), allowTarget);
}
