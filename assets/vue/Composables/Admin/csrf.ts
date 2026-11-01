import type { AxiosRequestConfig } from 'axios';

/**
 * Header HTTP lu par AppAdminController::checkCsrf()
 */
export const CSRF_HEADER = 'X-CSRF-TOKEN';

/**
 * Configuration axios portant le jeton CSRF d'une action admin
 * @param token jeton CSRF généré côté serveur ; sans jeton, aucune entête n'est ajoutée
 * @param config configuration axios à compléter
 */
export const withCsrf = (token?: string, config: AxiosRequestConfig = {}): AxiosRequestConfig => {
  if (!token) {
    return config;
  }
  return { ...config, headers: { ...(config.headers ?? {}), [CSRF_HEADER]: token } };
};
