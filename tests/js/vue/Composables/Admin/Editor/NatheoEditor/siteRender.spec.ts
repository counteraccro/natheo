import { describe, expect, it } from 'vitest';
import type { Router, RouteLocationNormalized } from 'vue-router';
import { loadSiteStylesheet, useSiteRender } from '@/vue/Composables/Admin/Editor/NatheoEditor/siteRender';

/** Routeur réduit à afterEach, qui garde ses fonctions pour les appeler */
const fakeRouter = () => {
  const hooks: ((to: RouteLocationNormalized, from: RouteLocationNormalized) => void)[] = [];
  const router = { afterEach: (hook: (typeof hooks)[number]) => hooks.push(hook) } as unknown as Router;
  const go = (from: string, to: string) =>
    hooks.forEach((hook) => hook({ path: to } as RouteLocationNormalized, { path: from } as RouteLocationNormalized));
  return { router, go, hooks };
};

describe('useSiteRender', () => {
  it("partage le choix et l'oublie au changement d'écran seulement", () => {
    const { router, go, hooks } = fakeRouter();
    const first = useSiteRender(router);
    const second = useSiteRender(router);
    expect(hooks).toHaveLength(1);
    first.value = true;
    expect(second.value).toBe(true);
    go('/admin/fr/faq/1', '/admin/fr/faq/1');
    expect(first.value).toBe(true);
    go('/admin/fr/faq/1', '/admin/fr/tags');
    expect(first.value).toBe(false);
  });
});

describe('loadSiteStylesheet', () => {
  it('ajoute la feuille une seule fois', () => {
    loadSiteStylesheet('/build/theme.css');
    loadSiteStylesheet('/build/theme.css');
    expect(document.head.querySelectorAll('link[href="/build/theme.css"]')).toHaveLength(1);
  });
});
