/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Adresses acceptées par Natheo-Editor dans les liens et les images
 */

/** Liens : web, e-mail, téléphone, adresse du site, page P#id, mot-clé d'e-mail [[x]] */
const LINK_PATTERNS = [
  /^https?:\/\/\S+$/i,
  /^mailto:\S+$/i,
  /^tel:[\d\s+().-]+$/i,
  /^\/(?!\/)\S*$/,
  /^P#\d+$/,
  /^\[\[[\w.-]+\]\]$/,
];

/** Images : web ou adresse du site */
const IMAGE_PATTERNS = [/^https?:\/\/\S+$/i, /^\/(?!\/)\S*$/];

/**
 * Lien autorisé (javascript:, data: et les autres protocoles refusés)
 * @param url
 */
export function isAllowedLink(url: string): boolean {
  const value = url.trim();
  return LINK_PATTERNS.some((pattern) => pattern.test(value));
}

/**
 * Source d'image autorisée
 * @param url
 */
export function isAllowedImage(url: string): boolean {
  const value = url.trim();
  return IMAGE_PATTERNS.some((pattern) => pattern.test(value));
}
