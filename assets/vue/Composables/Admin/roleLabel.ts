/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Nom traduit du rôle le plus élevé d'un utilisateur
 */
import type { Translator } from '@/vue/Composables/Admin/useTranslate';

/**
 * Nom du rôle le plus élevé parmi les rôles du back-office, le premier rôle brut à défaut
 * @param roles rôles bruts (ROLE_ADMIN…)
 * @param t traducteur de l'application
 */
export function roleLabel(roles: string[], t: Translator['t']): string {
  const labels: [string, () => string][] = [
    ['ROLE_SUPER_ADMIN', () => t('admin_app.role.super_admin')],
    ['ROLE_ADMIN', () => t('admin_app.role.admin')],
    ['ROLE_CONTRIBUTEUR', () => t('admin_app.role.contributor')],
    ['ROLE_USER', () => t('admin_app.role.user')],
  ];
  return labels.find(([role]) => roles.includes(role))?.[1]() ?? roles[0] ?? '';
}
