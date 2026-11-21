import { describe, expect, it, vi } from 'vitest';
import { defineComponent, h } from 'vue';
import { flushPromises, mount } from '@vue/test-utils';
import { createMemoryHistory, createRouter } from 'vue-router';
import AdminApp from '@/vue/Components/Admin/App/AdminApp.vue';
import { ADMIN_CONTEXT, createAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { useConfirm } from '@/vue/Composables/Admin/useConfirm';
import { NOT_FOUND_ROUTE } from '@/vue/Composables/Admin/adminRouter';
import { defineAdminTheme } from '@/vue/Composables/Admin/defineAdminTheme';
import { bootstrap } from '@/vue/Composables/Admin/__tests__/support';

/** Thème de test : affiche les props reçues autour de l'écran */
const theme = defineAdminTheme({
  id: 'test',
  layout: defineComponent({
    props: ['items', 'section', 'shortcuts', 'bootstrap'],
    setup(props, { slots }) {
      return () => h('div', { class: 'layout', 'data-section': props.section?.label ?? '' }, slots.default?.());
    },
  }),
  notFound: defineComponent({ render: () => h('p', { class: 'not-found' }) }),
});

/** Écran qui ne lit sa prop qu'à sa création, comme AdminScreen */
const SharedScreen = defineComponent({
  props: { kind: { type: String, required: true } },
  setup(props) {
    const kind = props.kind;
    return () => h('p', { class: 'shared' }, kind);
  },
});

async function mountApp(path = '/') {
  const router = createRouter({
    history: createMemoryHistory('/admin/fr/'),
    routes: [
      { path: '/', component: defineComponent({ render: () => h('p', { class: 'screen' }, 'Écran') }) },
      { path: '/tags', component: SharedScreen, props: { kind: 'collection' } },
      { path: '/tags/new', component: SharedScreen, props: { kind: 'form' } },
      { path: '/:path(.*)*', name: NOT_FOUND_ROUTE, component: theme.notFound },
    ],
  });
  const context = createAdminContext(bootstrap());
  const onClick = vi.spyOn(context.leave, 'onClick');
  const onBeforeUnload = vi.spyOn(context.leave, 'onBeforeUnload');
  await router.push(path);
  const wrapper = mount(AdminApp, {
    props: { theme },
    attachTo: document.body,
    global: { plugins: [router], provide: { [ADMIN_CONTEXT as symbol]: context } },
  });
  await router.isReady();
  return { wrapper, router, onClick, onBeforeUnload };
}

describe('AdminApp', () => {
  it('affiche l’écran courant dans le layout du thème et écoute les départs de la page', async () => {
    const { wrapper, onClick, onBeforeUnload } = await mountApp();
    expect(wrapper.find('.layout .screen').exists()).toBe(true);
    document.body.click();
    expect(onClick).toHaveBeenCalled();
    window.dispatchEvent(new Event('beforeunload'));
    expect(onBeforeUnload).toHaveBeenCalled();
  });

  it('recrée l’écran quand une autre route monte le même composant', async () => {
    const { wrapper, router } = await mountApp('/tags');
    expect(wrapper.find('.shared').text()).toBe('collection');
    await router.push('/tags/new');
    await flushPromises();
    expect(wrapper.find('.shared').text()).toBe('form');
  });

  it('nomme la page introuvable dans la section courante et l’onglet du navigateur', async () => {
    const { wrapper } = await mountApp('/inconnue');
    expect(wrapper.find('.not-found').exists()).toBe(true);
    expect(wrapper.find('.layout').attributes('data-section')).toBe('admin_app.not_found.title');
    expect(document.title).toBe(`admin_app.not_found.title – ${bootstrap().site.name}`);
  });

  it('affiche la confirmation demandée et transmet la réponse', async () => {
    await mountApp();
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
