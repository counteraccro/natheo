/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Contexte de l'application du back-office : données de démarrage, client API, traductions, menu, droits et départ
 */
import { inject, ref, type InjectionKey, type Ref } from 'vue';
import { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import { createAdminAccessToken, type AdminAccessTokenOptions } from '@/vue/Composables/Admin/adminAccessToken';
import { createAdminLeave, type AdminLeave } from '@/vue/Composables/Admin/adminLeave';
import { createAdminMenu, type AdminMenu } from '@/vue/Composables/Admin/useAdminMenu';
import { createCan, type AdminCan } from '@/vue/Composables/Admin/useCan';
import { useConfirm } from '@/vue/Composables/Admin/useConfirm';
import { createTranslator, type Translator } from '@/vue/Composables/Admin/useTranslate';
import type { AdminBootstrap } from '@/ts/Admin/App/AdminBootstrap.type';
import type { AdminPage } from '@/ts/Admin/App/AdminPage.type';
import type { AdminTranslationCatalogue } from '@/ts/Admin/Api/AdminTranslationCatalogue.type';

export interface AdminContext {
  bootstrap: AdminBootstrap;
  api: ApiClient;
  translator: Translator;
  /** Raccourci de translator.t */
  t: Translator['t'];
  menu: AdminMenu;
  can: AdminCan;
  leave: AdminLeave;
  /** Écran affiché (titre, icône), renseigné par les écrans décrits par des données */
  page: Ref<AdminPage | null>;
}

export const ADMIN_CONTEXT: InjectionKey<AdminContext> = Symbol('natheo.admin.context');

/**
 * Crée le contexte, fourni à toute l'application par l'entrée admin_app
 * @param bootstrap données de démarrage lues dans la page
 * @param options rechargement et horloge du token, remplacés dans les tests
 */
export function createAdminContext(bootstrap: AdminBootstrap, options: AdminAccessTokenOptions = {}): AdminContext {
  const accessToken = createAdminAccessToken(bootstrap.api, bootstrap.api.tokenUrl, options);
  const api = new ApiClient({
    baseUrl: bootstrap.api.prefix,
    locale: bootstrap.locale,
    token: () => accessToken.token(),
    onUnauthorized: () => accessToken.renew(),
  });
  const translator = createTranslator(bootstrap.locale, bootstrap.messages, async (domain) => {
    const catalogue = await api.get<AdminTranslationCatalogue>('/admin/translations/' + encodeURIComponent(domain));
    return catalogue.messages;
  });
  const t = translator.t;
  const menu = createAdminMenu(api, bootstrap.basePath);

  return {
    bootstrap,
    api,
    translator,
    t,
    menu,
    can: createCan(bootstrap.user.roles, menu),
    leave: createAdminLeave({
      enabled: bootstrap.options.confirmLeave,
      confirm: useConfirm().confirm,
      request: () => ({
        title: t('admin_app.leave.title'),
        message: t('admin_app.leave.message'),
        tone: 'danger',
        icon: 'exclamation-triangle',
        confirmLabel: t('admin_app.leave.confirm'),
        confirmIcon: 'logout',
      }),
    }),
    page: ref(null),
  };
}

/**
 * Contexte de l'application, lu par ses composants
 */
export function useAdminContext(): AdminContext {
  const context = inject(ADMIN_CONTEXT, null);
  if (context === null) {
    throw new Error("useAdminContext() est appelé hors de l'application du back-office");
  }
  return context;
}
