import { afterEach, describe, expect, it } from 'vitest';
import { defineComponent } from 'vue';
import { createMemoryHistory, createRouter } from 'vue-router';
import { createAdminLinks } from '@/vue/Composables/Admin/adminLinks';
import { NOT_FOUND_ROUTE } from '@/vue/Composables/Admin/adminRouter';

const Screen = defineComponent({ render: () => null });

function setup() {
  const router = createRouter({
    history: createMemoryHistory('/admin/fr/'),
    routes: [
      { path: '/', name: 'home', component: Screen },
      { path: '/tags', name: 'tag_index', component: Screen },
      { path: '/:path(.*)*', name: NOT_FOUND_ROUTE, component: Screen },
    ],
  });
  const onClick = createAdminLinks(router, '/admin/fr/');
  return { router, onClick };
}

/** Clique sur un lien ; renvoie true si l'écouteur a retenu la navigation */
function click(onClick: (event: MouseEvent) => void, href: string, init: MouseEventInit = {}): boolean {
  const link = document.createElement('a');
  link.href = href;
  document.body.append(link);
  let prevented = false;
  const record = (event: Event): void => {
    prevented = event.defaultPrevented;
    event.preventDefault();
  };
  document.addEventListener('click', onClick);
  window.addEventListener('click', record);
  link.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true, button: 0, ...init }));
  document.removeEventListener('click', onClick);
  window.removeEventListener('click', record);
  return prevented;
}

afterEach(() => {
  document.body.innerHTML = '';
});

describe('createAdminLinks', () => {
  it('ouvre un écran d’un module par le routeur', async () => {
    const { router, onClick } = setup();
    expect(click(onClick, '/admin/fr/tags?page=2')).toBe(true);
    await router.isReady();
    await new Promise((resolve) => setTimeout(resolve));
    expect(router.currentRoute.value.fullPath).toBe('/tags?page=2');
  });

  it('laisse recharger les pages Twig, la racine, les autres sites et les clics modifiés', () => {
    const { onClick } = setup();
    expect(click(onClick, '/admin/fr/page/index')).toBe(false);
    expect(click(onClick, '/admin/fr/')).toBe(false);
    expect(click(onClick, '/admin/en/tags')).toBe(false);
    expect(click(onClick, 'https://example.org/admin/fr/tags')).toBe(false);
    expect(click(onClick, '/admin/fr/tags', { metaKey: true })).toBe(false);
  });
});
