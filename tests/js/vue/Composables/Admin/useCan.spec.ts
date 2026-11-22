import { describe, expect, it } from 'vitest';
import { createAdminMenu } from '@/vue/Composables/Admin/useAdminMenu';
import { createCan } from '@/vue/Composables/Admin/useCan';
import { menuApi, moduleOf } from '@tests/vue/Composables/Admin/support';

async function canFor(roles: string[]) {
  const { api } = menuApi([moduleOf('tag', ['admin_tag_get_collection', 'admin_tag_patch'])], []);
  const menu = createAdminMenu(api, '/admin/fr/');
  await menu.load();
  return createCan(roles, menu);
}

describe('createCan', () => {
  it('permet une opération d’un module visible', async () => {
    const can = await canFor(['ROLE_CONTRIBUTEUR']);
    expect(can.canOperation('admin_tag_patch')).toBe(true);
    expect(can.canOperation('admin_tag_delete')).toBe(false);
  });

  it('applique les droits portés par l’élément', async () => {
    const can = await canFor(['ROLE_CONTRIBUTEUR']);
    expect(can.can('edit', 'admin_tag_patch')).toBe(true);
    expect(can.can('edit', 'admin_tag_patch', { permissions: { edit: true } })).toBe(true);
    expect(can.can('edit', 'admin_tag_patch', { permissions: { edit: false } })).toBe(false);
    expect(can.can('delete', 'admin_tag_delete', { permissions: { delete: true } })).toBe(false);
  });

  it('lit les rôles atteignables donnés par le serveur', async () => {
    const contributor = await canFor(['ROLE_CONTRIBUTEUR', 'ROLE_USER']);
    expect(contributor.hasRole('ROLE_USER')).toBe(true);
    expect(contributor.hasRole('ROLE_ADMIN')).toBe(false);
  });
});
