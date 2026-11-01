/**
 * Règle des liens externes des éléments de menu, identique à MenuService::isAllowedExternalLink()
 * Autorisés : http(s), mailto, tel, chemin relatif « /… », ou valeur vide / « # » (élément lié à une page)
 * @param link lien saisi
 */
export const isAllowedExternalLink = (link?: string | null): boolean => {
  const value = (link ?? '').trim();
  if (value === '' || value === '#') {
    return true;
  }
  // « //domaine » est une URL externe sans schéma : refusée comme chemin relatif
  return /^(https?:\/\/|mailto:|tel:|\/(?!\/))/i.test(value);
};
