/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Choix du thème du back-office depuis le menu Apparence : option de l'utilisateur enregistrée, puis rechargement
 */
import { notify } from '@/vue/Composables/Global/toast';
import { ApiError } from '@/vue/Composables/Global/Api/ApiError';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { AdminLeave } from '@/vue/Composables/Admin/adminLeave';

/** Option de l'utilisateur qui porte son thème */
export const ADMIN_THEME_OPTION = '/admin/user_options/ou_admin_theme';

export interface AdminThemeChoice {
  /** Thème de l'utilisateur */
  current: string;
  /** Enregistre le thème et recharge la page ; false si l'utilisateur garde son formulaire ou en cas d'erreur */
  choose(id: string): Promise<boolean>;
}

export interface AdminThemeChoiceDeps {
  api: ApiClient;
  leave: Pick<AdminLeave, 'confirmLeave' | 'allowUnload'>;
  current: string;
  /** Rechargement de la page, remplacé dans les tests */
  reload?: () => void;
}

/**
 * Crée le choix du thème
 * @param deps
 */
export function createAdminThemeChoice(deps: AdminThemeChoiceDeps): AdminThemeChoice {
  const reload = deps.reload ?? (() => window.location.reload());
  return {
    current: deps.current,
    async choose(id: string): Promise<boolean> {
      if (id === deps.current || !(await deps.leave.confirmLeave())) return false;
      try {
        const option = await deps.api.getResource<{ value: string }>(ADMIN_THEME_OPTION);
        await deps.api.patch(ADMIN_THEME_OPTION, { value: id }, { ifMatch: option.etag });
      } catch (caught) {
        const error = caught instanceof ApiError ? caught : ApiError.fromNetwork(caught);
        notify('danger', error.title, error.detail ?? undefined);
        return false;
      }
      deps.leave.allowUnload();
      reload();
      return true;
    },
  };
}

/**
 * Choix du thème de l'utilisateur connecté
 */
export function useAdminThemeChoice(): AdminThemeChoice {
  const { api, leave, bootstrap } = useAdminContext();
  return createAdminThemeChoice({ api, leave, current: bootstrap.theme });
}
