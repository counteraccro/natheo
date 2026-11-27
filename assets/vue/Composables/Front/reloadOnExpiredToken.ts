/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Rechargement de la page quand le token de l'utilisateur connecté expire, pour que le serveur le renouvelle
 */

/** Heure du dernier rechargement fait pour un token expiré */
export const RELOAD_KEY = 'natheo.front.reload_on_401';

/** Délai pendant lequel un nouveau 401 ne recharge plus la page, pour ne pas boucler */
export const RELOAD_DELAY = 60_000;

/**
 * Recharge la page une fois ; renvoie toujours false, la requête n'est pas rejouée
 * @param userToken access token de l'utilisateur connecté, chaîne vide sinon
 * @param reload rechargement de la page
 */
export function reloadOnExpiredToken(userToken: string, reload: () => void = () => window.location.reload()): boolean {
  const last = Number(sessionStorage.getItem(RELOAD_KEY) ?? 0);
  if (userToken === '' || Date.now() - last < RELOAD_DELAY) {
    return false;
  }
  sessionStorage.setItem(RELOAD_KEY, String(Date.now()));
  reload();
  return false;
}
