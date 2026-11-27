import { describe, expect, it, vi } from 'vitest';
import { defineComponent } from 'vue';
import { createMemoryHistory } from 'vue-router';
import { createAdminRouter, NOT_FOUND_ROUTE } from '@/vue/Composables/Admin/adminRouter';
import { defineAdminModule } from '@/vue/Composables/Admin/defineAdminModule';
import { createAdminMenu } from '@/vue/Composables/Admin/useAdminMenu';
import { createTranslator } from '@/vue/Composables/Admin/useTranslate';
import { menuApi, moduleOf } from '@tests/vue/Composables/Admin/support';

const Screen = defineComponent({ render: () => null });
const NotFound = defineComponent({ render: () => null });

function routerWith(
  options: {
    visible?: string[];
    operations?: string[];
    leave?: boolean;
    debug?: boolean;
    pages?: Record<string, object>;
  } = {}
) {
  const { api } = menuApi(
    (options.visible ?? ['tag']).map((id) => moduleOf(id, options.operations ?? ['admin_tag_post'])),
    []
  );
  const loader = vi.fn().mockResolvedValue({});
  const translator = createTranslator('fr', {}, loader);
  const confirmLeave = vi.fn().mockResolvedValue(options.leave ?? true);
  const assign = vi.fn();
  const router = createAdminRouter({
    basePath: '/admin/fr/',
    modules: [
      defineAdminModule({
        id: 'tag',
        domains: ['tag'],
        routes: [
          { path: '/tags', name: 'tag_index', component: Screen },
          { path: '/tags/new', name: 'tag_new', component: Screen, meta: { operation: 'admin_tag_post' } },
        ],
      }),
      defineAdminModule({
        id: 'design_system',
        debug: true,
        routes: [{ path: '/design-system', name: 'design_system', component: Screen }],
      }),
    ],
    debug: options.debug ?? false,
    menu: createAdminMenu(api, '/admin/fr/'),
    translator,
    notFound: NotFound,
    pages: options.pages,
    dashboardUrl: '/admin/fr/dashboard/index',
    confirmLeave,
    history: createMemoryHistory('/admin/fr/'),
    assign,
  });
  return { router, loader, confirmLeave, assign };
}

describe('createAdminRouter', () => {
  it('charge les domaines du module avant d’afficher son écran', async () => {
    const { router, loader } = routerWith();
    await router.push('/tags');
    expect(router.currentRoute.value.name).toBe('tag_index');
    expect(router.currentRoute.value.meta.module).toBe('tag');
    expect(loader).toHaveBeenCalledWith('tag');
  });

  it('affiche la page introuvable à la même adresse pour un module masqué', async () => {
    const { router } = routerWith({ visible: [] });
    await router.push('/tags?page=2');
    expect(router.currentRoute.value.name).toBe(NOT_FOUND_ROUTE);
    expect(router.currentRoute.value.fullPath).toBe('/tags?page=2');
  });

  it('affiche la page introuvable quand l’opération de l’écran est désactivée', async () => {
    const allowed = routerWith().router;
    await allowed.push('/tags/new');
    expect(allowed.currentRoute.value.name).toBe('tag_new');

    const { router } = routerWith({ operations: [] });
    await router.push('/tags/new');
    expect(router.currentRoute.value.name).toBe(NOT_FOUND_ROUTE);
    expect(router.currentRoute.value.fullPath).toBe('/tags/new');
  });

  it('affiche la page introuvable pour une adresse inconnue', async () => {
    const { router } = routerWith();
    await router.push('/inconnue/page');
    expect(router.currentRoute.value.name).toBe(NOT_FOUND_ROUTE);
  });

  it('reste sur l’écran quand le départ du formulaire est refusé', async () => {
    const { router, confirmLeave } = routerWith({ leave: false });
    await router.push('/tags');
    expect(confirmLeave).not.toHaveBeenCalled();
    await router.push('/inconnue');
    expect(confirmLeave).toHaveBeenCalledTimes(1);
    expect(router.currentRoute.value.name).toBe('tag_index');
  });

  it('ne demande pas de confirmer le départ quand seule l’adresse de la liste change', async () => {
    const { router, confirmLeave } = routerWith({ leave: false });
    await router.push('/tags');
    await router.replace({ query: { search: 'été', page: '2' } });
    expect(confirmLeave).not.toHaveBeenCalled();
    expect(router.currentRoute.value.fullPath).toBe('/tags?search=%C3%A9t%C3%A9&page=2');
  });

  it('envoie la racine vers le tableau de bord', async () => {
    const { router, assign } = routerWith();
    await router.push('/');
    expect(assign).toHaveBeenCalledWith('/admin/fr/dashboard/index');
  });

  it('déclare les outils de dev en dev seulement, sans module du registre', async () => {
    const dev = routerWith({ debug: true, visible: [] });
    await dev.router.push('/design-system');
    expect(dev.router.currentRoute.value.name).toBe('design_system');
    expect(dev.router.currentRoute.value.meta.module).toBeUndefined();

    const prod = routerWith({ debug: false });
    await prod.router.push('/design-system');
    expect(prod.router.currentRoute.value.name).toBe(NOT_FOUND_ROUTE);
  });

  it('remplace l’écran d’une route redéfinie par le thème, garde celui du module sinon', async () => {
    const ThemePage = defineComponent({ render: () => null });
    const themed = routerWith({ pages: { tag_index: ThemePage } });
    await themed.router.push('/tags');
    expect(themed.router.currentRoute.value.matched[0].components?.default).toBe(ThemePage);

    const plain = routerWith();
    await plain.router.push('/tags');
    expect(plain.router.currentRoute.value.matched[0].components?.default).toBe(Screen);
  });
});
