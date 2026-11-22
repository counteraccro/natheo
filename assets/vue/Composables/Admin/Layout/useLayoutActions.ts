/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Données et actions communes aux layouts des thèmes : utilisateur, usurpation, barre de debug, recherche globale,
 * notifications, choix de la disposition et ouverture d'une adresse
 */
import { computed, type ComputedRef } from 'vue';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { useAdminThemeChoice, type AdminThemeChoice } from '@/vue/Composables/Admin/useAdminThemeChoice';
import type { AdminBootstrap } from '@/ts/Admin/App/AdminBootstrap.type';
import type { DebugSection, DebugUser } from '@/ts/Admin/Layout/AdminDebugBar.type';
import type { SidebarUser } from '@/ts/Admin/Layout/AdminSidebar.type';

export interface LayoutActions {
  user: ComputedRef<SidebarUser>;
  /** Utilisateur usurpé et lien de retour, undefined hors usurpation */
  impersonating: ComputedRef<{ name: string; exitHref: string } | undefined>;
  debugSections: ComputedRef<DebugSection[]>;
  debugUsers: ComputedRef<DebugUser[]>;
  /** Recherche globale, envoyée à la page Twig des résultats */
  submitSearch(term: string): Promise<void>;
  openNotifications(): Promise<void>;
  /** Ouvre une adresse comme un clic sur un lien : routeur pour un écran de l'application, rechargement sinon */
  open(href: string): void;
  themeChoice: AdminThemeChoice;
}

/**
 * Actions du layout
 * @param bootstrap
 */
export function useLayoutActions(bootstrap: AdminBootstrap): LayoutActions {
  const { t, leave } = useAdminContext();

  const debugSections = computed<DebugSection[]>(() => {
    const debug = bootstrap.debug;
    if (debug === null) return [];
    return [
      {
        title: t('admin_app.debug.system'),
        entries: [
          { label: t('admin_app.debug.php'), value: debug.php },
          { label: t('admin_app.debug.environment'), value: debug.env },
          { label: t('admin_app.debug.database'), value: debug.database },
        ],
      },
      {
        title: t('admin_app.debug.git'),
        entries: [
          { label: t('admin_app.debug.branch'), value: debug.branch ?? '' },
          { label: t('admin_app.debug.commit'), value: debug.commit ?? '' },
          { label: t('admin_app.debug.commit_date'), value: debug.commitDate ?? '' },
        ],
      },
      {
        title: t('admin_app.debug.application'),
        entries: [{ label: t('admin_app.debug.version'), value: debug.version }],
      },
    ];
  });

  return {
    user: computed(() => ({
      name: bootstrap.user.name,
      detail: bootstrap.user.role,
      initials: bootstrap.user.initials,
    })),
    impersonating: computed(() => {
      const { user, urls } = bootstrap;
      return user.impersonating !== null && urls.impersonationExit !== null
        ? { name: user.impersonating, exitHref: urls.impersonationExit }
        : undefined;
    }),
    debugSections,
    debugUsers: computed(() =>
      (bootstrap.debug?.users ?? []).map((debugUser) => ({
        name: debugUser.login,
        detail: debugUser.role,
        initials: debugUser.login.slice(0, 2).toUpperCase(),
        href: debugUser.url,
        current: debugUser.id === bootstrap.user.id,
      }))
    ),
    async submitSearch(term: string): Promise<void> {
      if (term.trim() === '' || !(await leave.confirmLeave())) return;
      const form = document.createElement('form');
      form.method = 'post';
      form.action = bootstrap.urls.search;
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'global-search-input';
      input.value = term;
      form.append(input);
      document.body.append(form);
      form.submit();
    },
    async openNotifications(): Promise<void> {
      if (await leave.confirmLeave()) {
        window.location.assign(bootstrap.urls.notifications);
      }
    },
    open(href: string): void {
      // Le clic passe par les écoutes de l'application (routeur, confirmation de départ)
      const link = document.createElement('a');
      link.href = href;
      document.body.append(link);
      link.click();
      link.remove();
    },
    themeChoice: useAdminThemeChoice(),
  };
}
