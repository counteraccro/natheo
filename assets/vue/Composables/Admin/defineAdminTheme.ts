/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Déclaration d'un thème du back-office (theme.ts du dossier du thème)
 */
import type { AdminTheme } from '@/ts/Admin/Theme/AdminTheme.type';

/**
 * Déclare un thème, avec typage
 * @param theme
 */
export function defineAdminTheme(theme: AdminTheme): AdminTheme {
  return theme;
}
