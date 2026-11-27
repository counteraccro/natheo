import { afterEach, describe, expect, it, vi } from 'vitest';
import { ADMIN_THEME_OPTION, createAdminThemeChoice } from '@/vue/Composables/Admin/useAdminThemeChoice';
import { ApiError } from '@/vue/Composables/Global/Api/ApiError';
import { notificationState } from '@/vue/Composables/Global/toast';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';

function choiceWith(confirmed: boolean, api: Partial<ApiClient> = {}) {
  const leave = { confirmLeave: vi.fn().mockResolvedValue(confirmed), allowUnload: vi.fn() };
  const reload = vi.fn();
  const client = {
    getResource: vi.fn().mockResolvedValue({ data: { value: 'natheo_studio' }, etag: '"v1"' }),
    patch: vi.fn().mockResolvedValue({}),
    ...api,
  };
  const choice = createAdminThemeChoice({
    api: client as unknown as ApiClient,
    leave,
    current: 'natheo_studio',
    reload,
  });
  return { choice, leave, reload, client };
}

afterEach(() => notificationState.items.splice(0));

describe('createAdminThemeChoice', () => {
  it('enregistre le thème avec la version lue puis recharge sans redemander', async () => {
    const { choice, leave, reload, client } = choiceWith(true);
    expect(await choice.choose('natheo_atelier')).toBe(true);
    expect(client.patch).toHaveBeenCalledWith(ADMIN_THEME_OPTION, { value: 'natheo_atelier' }, { ifMatch: '"v1"' });
    expect(leave.allowUnload).toHaveBeenCalled();
    expect(reload).toHaveBeenCalled();
  });

  it('ne fait rien pour le thème courant ou quand l’utilisateur garde son formulaire', async () => {
    const same = choiceWith(true);
    expect(await same.choice.choose('natheo_studio')).toBe(false);
    expect(same.client.getResource).not.toHaveBeenCalled();

    const kept = choiceWith(false);
    expect(await kept.choice.choose('natheo_atelier')).toBe(false);
    expect(kept.client.patch).not.toHaveBeenCalled();
    expect(kept.reload).not.toHaveBeenCalled();
  });

  it('signale une erreur de l’API sans recharger', async () => {
    const { choice, reload } = choiceWith(true, {
      patch: vi.fn().mockRejectedValue(new ApiError(422, 'Données invalides', 'Valeur refusée')),
    });
    expect(await choice.choose('natheo_atelier')).toBe(false);
    expect(reload).not.toHaveBeenCalled();
    expect(notificationState.items[0]).toMatchObject({ type: 'danger', title: 'Données invalides' });
  });
});
