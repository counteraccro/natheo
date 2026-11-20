import { beforeEach, describe, expect, it, vi } from 'vitest';
import { RELOAD_DELAY, RELOAD_KEY, reloadOnExpiredToken } from '@/vue/Composables/Front/reloadOnExpiredToken';

describe('reloadOnExpiredToken', () => {
  beforeEach(() => sessionStorage.clear());

  it('recharge la page une fois quand un utilisateur est connecté', () => {
    const reload = vi.fn();
    expect(reloadOnExpiredToken('token', reload)).toBe(false);
    expect(reloadOnExpiredToken('token', reload)).toBe(false);
    expect(reload).toHaveBeenCalledOnce();
  });

  it('ne recharge pas sans utilisateur connecté', () => {
    const reload = vi.fn();
    reloadOnExpiredToken('', reload);
    expect(reload).not.toHaveBeenCalled();
  });

  it('recharge de nouveau passé le délai', () => {
    const reload = vi.fn();
    sessionStorage.setItem(RELOAD_KEY, String(Date.now() - RELOAD_DELAY - 1));
    reloadOnExpiredToken('token', reload);
    expect(reload).toHaveBeenCalledOnce();
  });
});
