/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Thème actif du back-office, fourni par la racine de l'application aux écrans décrits par des données
 */
import { defineAsyncComponent, inject, type Component, type InjectionKey } from 'vue';
import type { AdminTheme, ThemeComponent } from '@/ts/Admin/Theme/AdminTheme.type';

export const ADMIN_THEME: InjectionKey<AdminTheme> = Symbol('natheo.admin.theme');

/**
 * Thème actif
 */
export function useAdminTheme(): AdminTheme {
  const theme = inject(ADMIN_THEME, null);
  if (theme === null) {
    throw new Error("useAdminTheme() est appelé hors de l'application du back-office");
  }
  return theme;
}

/**
 * Composant affichable : une fonction est un chargement à la demande
 * @param component
 */
export function toComponent(component: ThemeComponent): Component {
  return typeof component === 'function' ? defineAsyncComponent(component as () => Promise<Component>) : component;
}
