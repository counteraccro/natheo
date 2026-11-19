/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Données de test de l'application du back-office : modules, entrées de menu et client simulé
 */
import { vi } from 'vitest';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { AdminModule } from '@/ts/Admin/Api/AdminModule.type';
import type { AdminMenuItem } from '@/ts/Admin/Api/AdminMenuItem.type';

export function moduleOf(id: string, operations: string[] = []): AdminModule {
  return {
    '@id': '/api/v2/admin/modules/' + id,
    '@type': 'AdminModule',
    id,
    label: id,
    description: '',
    locked: false,
    operations,
  };
}

export function itemOf(id: string, values: Partial<AdminMenuItem> = {}): AdminMenuItem {
  return {
    '@id': '/api/v2/admin/menu_items/' + id,
    '@type': 'AdminMenuItem',
    id,
    parent: null,
    module: null,
    label: id,
    icon: '',
    position: 0,
    legacyUrl: null,
    route: null,
    path: null,
    shortcut: false,
    hidden: false,
    badge: null,
    ...values,
  };
}

/** Client qui répond aux collections des modules et des entrées */
export function menuApi(modules: AdminModule[], items: AdminMenuItem[]) {
  const getCollection = vi.fn((path: string) => {
    const members = path === '/admin/modules' ? modules : items;
    return Promise.resolve({ members, total: members.length, page: 1, lastPage: 1 });
  });
  return { api: { getCollection } as unknown as ApiClient, getCollection };
}
