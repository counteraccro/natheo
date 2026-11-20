import { describe, expect, it } from 'vitest';
import { mergeModules, type AdminModuleDefinition } from '../defineAdminModule';

const module = (id: string, path: string): AdminModuleDefinition => ({
  id,
  routes: [{ path, name: `${id}_index`, component: {} }],
});

describe('mergeModules', () => {
  it('ajoute les modules de surcharge', () => {
    const merged = mergeModules([module('tag', '/tags')], [module('demo_overwrite', '/demo-overwrite')]);
    expect(merged.map((definition) => definition.id)).toEqual(['tag', 'demo_overwrite']);
  });

  it('remplace le module du cœur de même identifiant', () => {
    const merged = mergeModules([module('tag', '/tags'), module('faq', '/faqs')], [module('tag', '/mes-tags')]);
    expect(merged.map((definition) => [definition.id, definition.routes[0].path])).toEqual([
      ['faq', '/faqs'],
      ['tag', '/mes-tags'],
    ]);
  });
});
