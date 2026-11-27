/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Menu du back-office : modules et entrées visibles lus une fois dans l'API, arbre de la sidebar et raccourcis
 */
import { shallowRef, type ShallowRef } from 'vue';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { AdminModule } from '@/ts/Admin/Api/AdminModule.type';
import type { AdminMenuItem } from '@/ts/Admin/Api/AdminMenuItem.type';
import type { SidebarItem } from '@/ts/Admin/Layout/AdminSidebar.type';
import type { HeaderSection, HeaderShortcut } from '@/ts/Admin/Layout/AdminHeader.type';
import type { AdminPage } from '@/ts/Admin/App/AdminPage.type';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';

/** Route courante, réduite à ce que lit le menu */
export interface MenuLocation {
  name: string | null;
  /** Noms des routes du fil d'Ariane, de la racine à la page */
  breadcrumb?: string[];
  /** Écran affiché : son icône, et son titre quand sa route n'a pas d'entrée de menu (édition…) */
  page?: AdminPage | null;
  /** Paramètres de l'adresse, comparés aux filtres du chemin des entrées */
  query?: Record<string, unknown>;
}

export interface AdminMenu {
  modules: ShallowRef<AdminModule[]>;
  items: ShallowRef<AdminMenuItem[]>;
  /** Chargement des modules et des entrées, partagé par tous les appelants */
  load(): Promise<void>;
  /** Nouveau chargement, après un changement des modules activés */
  reload(): Promise<void>;
  isModuleVisible(id: string): boolean;
  /** L'opération appartient à un module visible et n'est pas désactivée */
  hasOperation(name: string): boolean;
  /** Arbre de la sidebar, entrée de la route courante active */
  tree(location: MenuLocation): SidebarItem[];
  /** Dossier et entrée de la route courante */
  section(location: MenuLocation): HeaderSection | null;
  /** Entrées du bouton « Nouveau » */
  shortcuts(): HeaderShortcut[];
}

/**
 * Crée le menu
 * @param api client de l'API
 * @param basePath /admin/<langue>/, préfixe des chemins des écrans de l'application
 */
export function createAdminMenu(api: ApiClient, basePath: string): AdminMenu {
  const modules = shallowRef<AdminModule[]>([]);
  const items = shallowRef<AdminMenuItem[]>([]);
  let loading: Promise<void> | null = null;

  const hrefOf = (item: AdminMenuItem): string | undefined =>
    item.path !== null ? basePath + item.path.replace(/^\//, '') : (item.legacyUrl ?? undefined);

  /** Filtres du chemin d'une entrée (comments?moderation=wait_validation) */
  const queryOf = (item: AdminMenuItem): [string, string][] => [
    ...new URLSearchParams(item.path?.split('?')[1] ?? '').entries(),
  ];

  /**
   * Entrée de la route courante, sinon la dernière route du fil d'Ariane qui a une entrée ; entre plusieurs entrées
   * de la même route, celle dont les filtres de l'adresse reprennent le plus de filtres de son chemin (un formulaire
   * garde l'adresse de sa liste)
   */
  const activeOf = (location: MenuLocation): AdminMenuItem | null => {
    const names = [...(location.breadcrumb ?? [])].reverse();
    if (location.name !== null) {
      names.unshift(location.name);
    }
    const query = location.query ?? {};
    for (const name of names) {
      const matching = items.value
        .filter((candidate) => candidate.route === name && !candidate.hidden)
        .filter((candidate) => queryOf(candidate).every(([key, value]) => query[key] === value));
      const item = matching.sort((a, b) => queryOf(b).length - queryOf(a).length)[0];
      if (item) return item;
    }
    return null;
  };

  const build = (parent: string | null, activeId: string | null): SidebarItem[] =>
    items.value
      .filter((item) => item.parent === parent && !item.hidden)
      .flatMap((item): SidebarItem[] => {
        const href = hrefOf(item);
        const children = build(item.id, activeId);
        // Dossier sans entrée visible : masqué
        if (href === undefined && children.length === 0) return [];
        return [
          {
            id: item.id,
            label: item.label,
            icon: item.icon || undefined,
            href,
            active: item.id === activeId || undefined,
            badge: item.badge ?? undefined,
            children: children.length > 0 ? children : undefined,
          },
        ];
      });

  const load = (): Promise<void> => {
    loading ??= Promise.all([
      api.getCollection<AdminModule>('/admin/modules'),
      api.getCollection<AdminMenuItem>('/admin/menu_items'),
    ]).then(
      ([loadedModules, loadedItems]) => {
        modules.value = loadedModules.members;
        // Déjà dans l'ordre du menu
        items.value = loadedItems.members;
      },
      (error: unknown) => {
        loading = null;
        throw error;
      }
    );
    return loading;
  };

  return {
    modules,
    items,
    load,
    reload(): Promise<void> {
      loading = null;
      return load();
    },
    isModuleVisible(id: string): boolean {
      return modules.value.some((module) => module.id === id);
    },
    hasOperation(name: string): boolean {
      return modules.value.some((module) => module.operations.includes(name));
    },
    tree(location: MenuLocation): SidebarItem[] {
      return build(null, activeOf(location)?.id ?? null);
    },
    section(location: MenuLocation): HeaderSection | null {
      const active = activeOf(location);
      if (active === null) return null;
      const parent = items.value.find((item) => item.id === active.parent);
      const page = location.page;
      const label = active.route !== location.name && page ? page.title : active.label;
      return page?.icon
        ? { group: parent?.label, label, iconName: page.icon }
        : { group: parent?.label, label, icon: active.icon || undefined };
    },
    shortcuts(): HeaderShortcut[] {
      return items.value.flatMap((item) => {
        const href = hrefOf(item);
        return item.shortcut && href !== undefined
          ? [{ id: item.id, label: item.label, href, icon: item.icon || undefined }]
          : [];
      });
    },
  };
}

/**
 * Menu de l'application, lu dans son contexte
 */
export function useAdminMenu(): AdminMenu {
  return useAdminContext().menu;
}
