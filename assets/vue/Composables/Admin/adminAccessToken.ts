/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Access token de la session API du back-office : gardé en mémoire, renouvelé par la page de l'application
 */
import type { AdminAccessToken } from '@/ts/Admin/App/AdminBootstrap.type';

/** Marge avant l'expiration sous laquelle le token est renouvelé avant l'appel */
const RENEW_MARGIN = 60_000;

export interface AdminAccessTokenStore {
  /** Token courant, renouvelé s'il expire bientôt */
  token(): Promise<string>;
  /** Redemande un token après un 401 ; false si la session PHP est perdue (page rechargée) ou le réseau coupé */
  renew(): Promise<boolean>;
}

export interface AdminAccessTokenOptions {
  /** Rechargement de la page, remplacé dans les tests */
  reload?: () => void;
  now?: () => number;
}

/**
 * Crée le gardien du token
 * @param initial token reçu avec la page
 * @param tokenUrl adresse de renouvellement (admin_app_token)
 * @param options
 */
export function createAdminAccessToken(
  initial: AdminAccessToken,
  tokenUrl: string,
  options: AdminAccessTokenOptions = {}
): AdminAccessTokenStore {
  const reload = options.reload ?? (() => window.location.reload());
  const now = options.now ?? (() => Date.now());
  let current = initial;
  // Une seule requête de renouvellement pour les appels lancés en même temps
  let pending: Promise<boolean> | null = null;

  const fetchToken = async (): Promise<boolean> => {
    let response: Response;
    try {
      response = await fetch(tokenUrl, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
        redirect: 'manual',
      });
    } catch {
      // Réseau coupé : l'appel échoue sans recharger, la saisie en cours est gardée
      return false;
    }
    if (response.ok && response.headers.get('Content-Type')?.includes('application/json')) {
      current = (await response.json()) as AdminAccessToken;
      return true;
    }
    // Le pare-feu renvoie vers la connexion au rechargement
    reload();
    return false;
  };

  const renew = (): Promise<boolean> => {
    pending ??= fetchToken().finally(() => {
      pending = null;
    });
    return pending;
  };

  return {
    async token(): Promise<string> {
      if (Date.parse(current.expiresAt) - now() < RENEW_MARGIN) {
        await renew();
      }
      return current.accessToken;
    },
    renew,
  };
}
