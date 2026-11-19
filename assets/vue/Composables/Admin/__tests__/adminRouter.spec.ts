import { describe, expect, it, vi } from 'vitest';
import { defineComponent } from 'vue';
import { createMemoryHistory } from 'vue-router';
import { createAdminRouter, NOT_FOUND_ROUTE } from '@/vue/Composables/Admin/adminRouter';
import { defineAdminModule } from '@/vue/Composables/Admin/defineAdminModule';
import { createAdminMenu } from '@/vue/Composables/Admin/useAdminMenu';
import { createTranslator } from '@/vue/Composables/Admin/useTranslate';
import { menuApi, moduleOf } from './support';

const Screen = defineComponent({ render: () => null });
const NotFound = defineComponent({ render: () => null });

function routerWith(options: { visible?: string[]; leave?: boolean; debug?: boolean } = {}) {
  const { api } = menuApi(
    (options.visible ?? ['tag']).map((id) => moduleOf(id)),
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
        routes: [{ path: '/tags', name: 'tag_index', component: Screen }],
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
});
