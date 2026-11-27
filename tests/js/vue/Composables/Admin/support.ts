/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Données de test de l'application du back-office : démarrage, modules, entrées de menu et client simulé
 */
import { vi } from 'vitest';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { AdminModule } from '@/ts/Admin/Api/AdminModule.type';
import type { AdminMenuItem } from '@/ts/Admin/Api/AdminMenuItem.type';
import type { AdminBootstrap } from '@/ts/Admin/App/AdminBootstrap.type';

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

/** Données de démarrage d'un administrateur, valeurs remplaçables */
export function bootstrap(values: Partial<AdminBootstrap> = {}): AdminBootstrap {
  return {
    api: {
      prefix: '/api/v2',
      accessToken: 'initial',
      expiresAt: '2099-01-01T00:00:00Z',
      tokenUrl: '/admin/fr/app/token',
    },
    locale: 'fr',
    locales: ['fr', 'en', 'es'],
    basePath: '/admin/fr/',
    user: {
      id: 1,
      login: 'admin',
      name: 'Ada',
      initials: 'A',
      email: 'a@b.c',
      avatar: null,
      roles: ['ROLE_ADMIN', 'ROLE_CONTRIBUTEUR', 'ROLE_USER'],
      role: 'Administrateur',
      impersonating: null,
    },
    site: { name: 'Nathéo', version: '2.1.0-dev', url: '/', theme: null },
    urls: {
      dashboard: '/admin/fr/dashboard/index',
      logout: '/admin/fr/logout',
      impersonationExit: null,
      myAccount: '/admin/fr/user/my-account',
      myOptions: '/admin/fr/user/my-options',
      search: '/admin/fr/search',
      notifications: '/admin/fr/notification/index',
    },
    options: {
      notifications: true,
      confirmLeave: true,
      itemsPerPage: 20,
      openComment: true,
      commentWaitValidation: true,
    },
    theme: 'natheo_studio',
    themes: [
      { id: 'natheo_studio', label: 'Natheo-Studio' },
      { id: 'natheo_atelier', label: 'Natheo-Atelier' },
    ],
    messages: { admin_app: { 'admin_app.leave.title': 'Quitter la page ?' } },
    debug: null,
    ...values,
  };
}
