/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Menu du back-office : modules et entrées visibles lus une fois dans l'API, arbre de la sidebar et raccourcis
 */
import { shallowRef, type ShallowRef } from 'vue';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { AdminModule } from '@/ts/Admin/Api/AdminModule.type';
import type { AdminMenuItem } from '@/ts/Admin/Api/AdminMenuItem.type';
import type { SidebarItem } from '@/ts/Admin/Layout/AdminSidebar.type';
import type { HeaderSection, HeaderShortcut } from '@/ts/Admin/Layout/AdminHeader.type';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';

/** Route courante, réduite à ce que lit le menu */
export interface MenuLocation {
  name: string | null;
  /** Noms des routes du fil d'Ariane, de la racine à la page */
  breadcrumb?: string[];
  /** Titre de l'écran, montré par la section quand sa route n'a pas d'entrée de menu (édition…) */
  title?: string | null;
}

export interface AdminMenu {
  modules: ShallowRef<AdminModule[]>;
  items: ShallowRef<AdminMenuItem[]>;
  /** Chargement des modules et des entrées, partagé par tous les appelants */
  load(): Promise<void>;
  isModuleVisible(id: string): boolean;
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

  /** Entrée de la route courante, sinon la dernière route du fil d'Ariane qui a une entrée */
  const activeOf = (location: MenuLocation): AdminMenuItem | null => {
    const names = [...(location.breadcrumb ?? [])].reverse();
    if (location.name !== null) {
      names.unshift(location.name);
    }
    for (const name of names) {
      const item = items.value.find((candidate) => candidate.route === name && !candidate.hidden);
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

  return {
    modules,
    items,
    load(): Promise<void> {
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
    },
    isModuleVisible(id: string): boolean {
      return modules.value.some((module) => module.id === id);
    },
    tree(location: MenuLocation): SidebarItem[] {
      return build(null, activeOf(location)?.id ?? null);
    },
    section(location: MenuLocation): HeaderSection | null {
      const active = activeOf(location);
      if (active === null) return null;
      const parent = items.value.find((item) => item.id === active.parent);
      const label = active.route !== location.name && location.title ? location.title : active.label;
      return { group: parent?.label, label, icon: active.icon || undefined };
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
