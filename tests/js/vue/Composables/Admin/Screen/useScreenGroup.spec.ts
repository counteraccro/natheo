import { describe, expect, it } from 'vitest';
import { createMemoryHistory, createRouter } from 'vue-router';
import { collectionRoute, formRoute } from '@/vue/Composables/Admin/defineAdminModule';
import { defineCollectionScreen, defineFormScreen } from '@/vue/Composables/Admin/Screen/defineScreen';
import { screenGroupOf } from '@/vue/Composables/Admin/Screen/useScreenGroup';
import type { ScreenItem } from '@/ts/Admin/Screen/Screen.type';

const list = defineCollectionScreen<ScreenItem>({
  resource: '/admin/tags',
  read: { operation: 'admin_tag_get_collection' },
  title: 'tag.index.title',
  icon: 'tag',
  columns: [],
  rowLabel: (item) => String(item.id),
  messages: { confirmDisable: 'cdi', confirmDelete: 'cd', disabled: 'di', enabled: 'e', deleted: 'd' },
});

const form = defineFormScreen<ScreenItem>({
  resource: '/admin/tags',
  read: { operation: 'admin_tag_get' },
  create: { operation: 'admin_tag_post' },
  title: { create: 'tag.form.title_new', edit: 'tag.form.title_edit' },
  back: 'tag_index',
  editRoute: 'tag_edit',
  leaveKey: 'tag',
  initial: () => ({}),
  sections: [],
  update: { operation: 'admin_tag_patch' },
  messages: { created: 'c', saved: 's', confirmDelete: 'cd', deleted: 'd' },
  rowLabel: () => '',
});

async function groupAt(path: string) {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      collectionRoute('/tags', 'tag_index', list),
      formRoute('/tags/:id(\\d+)', 'tag_edit', form),
      { path: '/autre', name: 'autre', component: {} },
    ],
  });
  await router.push(path);
  return screenGroupOf(router, router.currentRoute.value);
}

describe('screenGroupOf', () => {
  it('donne la liste du groupe depuis la liste et depuis un formulaire', async () => {
    expect(await groupAt('/tags')).toEqual({ name: 'tag_index', descriptor: list, isCollection: true });
    expect(await groupAt('/tags/3')).toEqual({ name: 'tag_index', descriptor: list, isCollection: false });
  });

  it('ne donne rien hors d’un écran décrit par des données', async () => {
    expect(await groupAt('/autre')).toBeNull();
  });
});
