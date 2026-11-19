import { describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import { createMemoryHistory, createRouter } from 'vue-router';
import NatheoStudioLayout from '@/vue/Components/Admin/Themes/NatheoStudio/NatheoStudioLayout.vue';
import NatheoStudioNotFound from '@/vue/Components/Admin/Themes/NatheoStudio/NatheoStudioNotFound.vue';
import { ADMIN_CONTEXT, createAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { buildUiLabels, UI_LABELS } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { bootstrap } from '@/vue/Composables/Admin/__tests__/support';
import type { AdminBootstrap } from '@/ts/Admin/App/AdminBootstrap.type';

function mountWith(component: object, props: Record<string, unknown>, data: AdminBootstrap = bootstrap()) {
  const context = createAdminContext(data);
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [{ path: '/', component: { render: () => null } }],
  });
  const wrapper = mount(component, {
    props,
    attachTo: document.body,
    slots: { default: '<p class="screen">Écran</p>' },
    global: {
      plugins: [router],
      provide: { [ADMIN_CONTEXT as symbol]: context, [UI_LABELS as symbol]: buildUiLabels((key) => key) },
    },
  });
  return { wrapper, context };
}

const layoutProps = (data: AdminBootstrap) => ({
  items: [{ id: 'page', label: 'Pages', href: '/admin/fr/page/index' }],
  section: { group: 'Contenus', label: 'Tags' },
  shortcuts: [{ id: 'tag_new', label: 'Nouveau tag', href: '/admin/fr/tags/new' }],
  bootstrap: data,
});

describe('NatheoStudioLayout', () => {
  it('assemble la sidebar, l’en-tête et l’écran', () => {
    const data = bootstrap();
    const { wrapper } = mountWith(NatheoStudioLayout, layoutProps(data), data);
    expect(wrapper.text()).toContain('Nathéo');
    expect(wrapper.text()).toContain('Administrateur');
    expect(wrapper.find('a[href="/admin/fr/page/index"]').exists()).toBe(true);
    expect(wrapper.find('main .screen').exists()).toBe(true);
    expect(wrapper.find('[aria-label="admin_ui.header.notifications"]').exists()).toBe(true);
    expect(wrapper.text()).not.toContain('admin_ui.debug_bar.open');
  });

  it('masque la cloche sans notifications et montre l’usurpation', () => {
    const data = bootstrap({
      options: { notifications: false, confirmLeave: true, itemsPerPage: 20 },
      user: { ...bootstrap().user, impersonating: 'Grace' },
      urls: { ...bootstrap().urls, impersonationExit: '/admin/fr/dashboard/index?_switch_user=_exit' },
    });
    const { wrapper } = mountWith(NatheoStudioLayout, layoutProps(data), data);
    expect(wrapper.find('[aria-label="admin_ui.header.notifications"]').exists()).toBe(false);
    expect(wrapper.find('a[href="/admin/fr/dashboard/index?_switch_user=_exit"]').exists()).toBe(true);
  });

  it('envoie la recherche globale à la page Twig', async () => {
    const submit = vi.spyOn(HTMLFormElement.prototype, 'submit').mockImplementation(() => {});
    const data = bootstrap();
    const { wrapper } = mountWith(NatheoStudioLayout, layoutProps(data), data);
    await wrapper.find('input[type="search"]').setValue('accueil');
    await wrapper.find('form[role="search"]').trigger('submit');
    await flushPromises();
    expect(submit).toHaveBeenCalledTimes(1);
    const form = submit.mock.contexts[0] as HTMLFormElement;
    expect(form.getAttribute('action')).toBe('/admin/fr/search');
    expect((form.elements.namedItem('global-search-input') as HTMLInputElement).value).toBe('accueil');
  });

  it('affiche la barre de debug en dev', () => {
    const data = bootstrap({
      debug: {
        php: '8.5',
        env: 'dev',
        database: 'Mysql',
        branch: 'feature/N-551',
        commit: 'abc',
        commitDate: '2026-11-19',
        version: '2.1.0-dev',
        users: [{ id: 1, login: 'admin', role: 'Super administrateur', avatar: null, url: '/login-link' }],
      },
    });
    const { wrapper } = mountWith(NatheoStudioLayout, layoutProps(data), data);
    expect(wrapper.find('[aria-label="admin_ui.debug_bar.open"]').exists()).toBe(true);
  });
});

describe('NatheoStudioNotFound', () => {
  it('montre l’adresse demandée et le lien vers le tableau de bord', () => {
    const { wrapper } = mountWith(NatheoStudioNotFound, {
      path: '/admin/fr/tagz/12',
      dashboardUrl: '/admin/fr/dashboard/index',
    });
    expect(wrapper.find('h1').text()).toBe('admin_app.not_found.title');
    expect(wrapper.find('code').text()).toBe('/admin/fr/tagz/12');
    expect(wrapper.find('a[href="/admin/fr/dashboard/index"]').text()).toContain('admin_app.not_found.dashboard');
  });
});
