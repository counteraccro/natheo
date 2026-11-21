import { describe, expect, it } from 'vitest';
import { createAdminMenu } from '@/vue/Composables/Admin/useAdminMenu';
import { itemOf, menuApi, moduleOf } from './support';

const items = [
  itemOf('content', { label: 'Contenus', icon: 'M1' }),
  itemOf('tag', { parent: 'content', module: 'tag', label: 'Tags', icon: 'M2' }),
  itemOf('tag_index', { parent: 'tag', module: 'tag', label: 'Liste', route: 'tag_index', path: 'tags' }),
  itemOf('tag_new', {
    parent: 'tag',
    module: 'tag',
    label: 'Nouveau tag',
    route: 'tag_new',
    path: 'tags/new',
    shortcut: true,
  }),
  itemOf('page', { parent: 'content', module: 'page', label: 'Pages', legacyUrl: '/admin/fr/page/index', badge: 2 }),
  itemOf('faq_new', { module: 'faq', legacyUrl: '/admin/fr/faq/add', shortcut: true, hidden: true }),
  itemOf('empty', { label: 'Dossier vide' }),
];

async function loadedMenu() {
  const { api, getCollection } = menuApi([moduleOf('tag'), moduleOf('page')], items);
  const menu = createAdminMenu(api, '/admin/fr/');
  await Promise.all([menu.load(), menu.load()]);
  return { menu, getCollection };
}

describe('createAdminMenu', () => {
  it('charge les modules et les entrées une seule fois', async () => {
    const { menu, getCollection } = await loadedMenu();
    expect(getCollection).toHaveBeenCalledTimes(2);
    expect(menu.isModuleVisible('tag')).toBe(true);
    expect(menu.isModuleVisible('faq')).toBe(false);
  });

  it('construit l’arbre sans les entrées masquées ni les dossiers vides', async () => {
    const { menu } = await loadedMenu();
    const tree = menu.tree({ name: null });
    expect(tree.map((item) => item.id)).toEqual(['content']);
    const [tag, page] = tree[0].children ?? [];
    expect(tag.children?.map((item) => [item.id, item.href])).toEqual([
      ['tag_index', '/admin/fr/tags'],
      ['tag_new', '/admin/fr/tags/new'],
    ]);
    expect(page).toMatchObject({ href: '/admin/fr/page/index', badge: 2 });
  });

  it('active l’entrée de la route courante, sinon la dernière route du fil d’Ariane', async () => {
    const { menu } = await loadedMenu();
    const activeIds = (tree: ReturnType<typeof menu.tree>): string[] =>
      tree.flatMap((item) => [...(item.active ? [item.id] : []), ...activeIds(item.children ?? [])]);

    expect(activeIds(menu.tree({ name: 'tag_new' }))).toEqual(['tag_new']);
    expect(activeIds(menu.tree({ name: 'tag_edit', breadcrumb: ['tag_index'] }))).toEqual(['tag_index']);
    expect(activeIds(menu.tree({ name: 'inconnue' }))).toEqual([]);
  });

  it('donne la section courante et les raccourcis', async () => {
    const { menu } = await loadedMenu();
    expect(menu.section({ name: 'tag_index' })).toEqual({ group: 'Tags', label: 'Liste', icon: undefined });
    expect(menu.section({ name: 'tag_index', title: 'Gestion des tags' })?.label).toBe('Liste');
    expect(menu.section({ name: 'tag_edit', breadcrumb: ['tag_index'], title: 'Édition du tag #4' })).toEqual({
      group: 'Tags',
      label: 'Édition du tag #4',
      icon: undefined,
    });
    expect(menu.section({ name: 'inconnue' })).toBeNull();
    expect(menu.shortcuts().map((shortcut) => [shortcut.id, shortcut.href])).toEqual([
      ['tag_new', '/admin/fr/tags/new'],
      ['faq_new', '/admin/fr/faq/add'],
    ]);
  });
});
