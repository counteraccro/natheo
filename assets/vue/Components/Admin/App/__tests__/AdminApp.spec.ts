import { describe, expect, it, vi } from 'vitest';
import { defineComponent } from 'vue';
import { flushPromises, mount } from '@vue/test-utils';
import { createMemoryHistory, createRouter } from 'vue-router';
import AdminApp from '@/vue/Components/Admin/App/AdminApp.vue';
import { ADMIN_CONTEXT, type AdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { useConfirm } from '@/vue/Composables/Admin/useConfirm';

function mountApp() {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [{ path: '/', component: defineComponent({ template: '<p class="screen">Écran</p>' }) }],
  });
  const leave = { confirmLeave: vi.fn(), onClick: vi.fn(), onBeforeUnload: vi.fn() };
  const context = { t: (key: string) => key, leave } as unknown as AdminContext;
  const wrapper = mount(AdminApp, {
    attachTo: document.body,
    global: { plugins: [router], provide: { [ADMIN_CONTEXT as symbol]: context } },
  });
  return { wrapper, router, leave };
}

describe('AdminApp', () => {
  it('affiche l’écran courant et écoute les départs de la page', async () => {
    const { wrapper, router, leave } = mountApp();
    await router.isReady();
    expect(wrapper.find('.screen').exists()).toBe(true);
    document.body.click();
    expect(leave.onClick).toHaveBeenCalled();
    window.dispatchEvent(new Event('beforeunload'));
    expect(leave.onBeforeUnload).toHaveBeenCalled();
  });

  it('affiche la confirmation demandée et transmet la réponse', async () => {
    const { router } = mountApp();
    await router.isReady();
    const answer = useConfirm().confirm({ title: 'Supprimer le tag ?', tone: 'danger' });
    await flushPromises();
    const dialog = document.body.querySelector('dialog');
    expect(dialog?.textContent).toContain('Supprimer le tag ?');
    const confirmButton = [...(dialog?.querySelectorAll('button') ?? [])].find((button) =>
      button.textContent?.includes('admin_ui.confirm_dialog.confirm')
    ) as HTMLButtonElement;
    confirmButton.click();
    expect(await answer).toBe(true);
  });
});
