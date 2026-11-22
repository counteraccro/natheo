import { afterEach, describe, expect, it, vi } from 'vitest';
import { createAdminLeave } from '@/vue/Composables/Admin/adminLeave';
import { leaveGuard } from '@/vue/Composables/Admin/leaveGuard';

const form = { title: 'Avant' };

function leaveWith(answer: boolean, enabled = true) {
  const confirm = vi.fn().mockResolvedValue(answer);
  const assign = vi.fn();
  const leave = createAdminLeave({ enabled, confirm, request: () => ({ title: 'Quitter ?' }), assign });
  return { leave, confirm, assign };
}

/** Clique sur un lien ; renvoie true si onClick a retenu la navigation, que happy-dom n'effectue jamais */
function clickOn(href: string, init: MouseEventInit = {}, target?: string): boolean {
  const link = document.createElement('a');
  link.href = href;
  if (target) link.target = target;
  document.body.append(link);
  let prevented = false;
  const record = (event: Event): void => {
    prevented = event.defaultPrevented;
    event.preventDefault();
  };
  window.addEventListener('click', record);
  link.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true, button: 0, ...init }));
  window.removeEventListener('click', record);
  return prevented;
}

function makeDirty(): void {
  leaveGuard.track('form', () => ({ ...form }));
  form.title = 'Après';
}

afterEach(() => {
  leaveGuard.untrack('form');
  form.title = 'Avant';
  document.body.innerHTML = '';
});

describe('createAdminLeave', () => {
  it('laisse partir sans question quand rien n’est modifié ou que l’option est coupée', async () => {
    const clean = leaveWith(false);
    expect(await clean.leave.confirmLeave()).toBe(true);
    makeDirty();
    const disabled = leaveWith(false, false);
    expect(await disabled.leave.confirmLeave()).toBe(true);
    expect(disabled.confirm).not.toHaveBeenCalled();
  });

  it('demande confirmation pour un formulaire modifié', async () => {
    makeDirty();
    const { leave, confirm } = leaveWith(false);
    expect(await leave.confirmLeave()).toBe(false);
    expect(confirm).toHaveBeenCalledWith({ title: 'Quitter ?' });
  });

  it('retient un lien qui recharge et l’ouvre après confirmation', async () => {
    makeDirty();
    const { leave, assign } = leaveWith(true);
    document.addEventListener('click', leave.onClick);
    const prevented = clickOn('/admin/fr/page/index');
    document.removeEventListener('click', leave.onClick);
    expect(prevented).toBe(true);
    await vi.waitFor(() => expect(assign).toHaveBeenCalledWith(expect.stringContaining('/admin/fr/page/index')));

    const unload = new Event('beforeunload', { cancelable: true }) as BeforeUnloadEvent;
    leave.onBeforeUnload(unload);
    expect(unload.defaultPrevented).toBe(false);
  });

  it('ignore les nouveaux onglets, les clics modifiés et les liens déjà pris en charge', () => {
    makeDirty();
    const { leave } = leaveWith(true);
    document.addEventListener('click', leave.onClick);
    expect(clickOn('/admin/fr/page/index', {}, '_blank')).toBe(false);
    expect(clickOn('/admin/fr/page/index', { ctrlKey: true })).toBe(false);
    document.removeEventListener('click', leave.onClick);
  });

  it('bloque la fermeture de l’onglet d’un formulaire modifié', () => {
    makeDirty();
    const { leave } = leaveWith(true);
    const unload = new Event('beforeunload', { cancelable: true }) as BeforeUnloadEvent;
    leave.onBeforeUnload(unload);
    expect(unload.defaultPrevented).toBe(true);
  });

  it('laisse recharger après un départ déjà accepté', () => {
    makeDirty();
    const { leave } = leaveWith(true);
    leave.allowUnload();
    const unload = new Event('beforeunload', { cancelable: true }) as BeforeUnloadEvent;
    leave.onBeforeUnload(unload);
    expect(unload.defaultPrevented).toBe(false);
  });
});
