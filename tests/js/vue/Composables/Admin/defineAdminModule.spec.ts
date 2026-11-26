import { describe, expect, it, vi } from 'vitest';
import {
  collectionRoute,
  defineAdminModule,
  formRoute,
  mergeModules,
  reloadMenuBadges,
  type AdminModuleDefinition,
} from '@/vue/Composables/Admin/defineAdminModule';
import { tagCollection, tagForm } from '@admin/Modules/Tag/screens';
import { notifyResourceChange } from '@/vue/Composables/Admin/Screen/resourceChanges';

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

describe('collectionRoute / formRoute', () => {
  it('donnent à chaque écran l’opération sans laquelle il est introuvable', () => {
    expect(collectionRoute('/tags', 'tag_index', tagCollection).meta?.operation).toBe('admin_tag_get_collection');
    expect(formRoute('/tags/new', 'tag_new', tagForm).meta?.operation).toBe('admin_tag_post');
    expect(formRoute('/tags/:id(\\d+)', 'tag_edit', tagForm).meta?.operation).toBe('admin_tag_get');
  });
});

describe('reloadMenuBadges', () => {
  it('recharge le menu une fois par écriture d’une ressource déclarée', () => {
    const menu = { reload: vi.fn().mockResolvedValue(undefined) };
    const stop = reloadMenuBadges(
      [
        defineAdminModule({ id: 'comment', badges: ['/admin/comments'], routes: [] }),
        defineAdminModule({ id: 'other', badges: ['/admin/comments'], routes: [] }),
      ],
      menu
    );
    notifyResourceChange('/admin/tags');
    expect(menu.reload).not.toHaveBeenCalled();
    notifyResourceChange('/admin/comments', { ids: [1] });
    expect(menu.reload).toHaveBeenCalledOnce();
    stop();
    notifyResourceChange('/admin/comments');
    expect(menu.reload).toHaveBeenCalledOnce();
  });
});
