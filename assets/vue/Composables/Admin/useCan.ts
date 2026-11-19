/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Droits côté écran : masquent les actions refusées, l'API reste la barrière
 */
import type { AdminMenu } from '@/vue/Composables/Admin/useAdminMenu';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';

export type ItemAction = 'edit' | 'disable' | 'delete';

/** Élément qui porte ses propres droits, renvoyés par l'API */
export interface ItemWithPermissions {
  permissions?: Partial<Record<ItemAction, boolean>>;
}

export interface AdminCan {
  /** L'opération d'API fait partie d'un module visible */
  canOperation(name: string): boolean;
  /** L'opération est permise et, si l'élément porte ses droits, l'action lui est permise */
  can(action: ItemAction, operation: string, item?: ItemWithPermissions | null): boolean;
  /** Le rôle est donné à l'utilisateur, directement ou par la hiérarchie des rôles */
  hasRole(role: string): boolean;
}

/** Rôles donnés par chaque rôle (role_hierarchy de security.yaml, rôles du back-office) */
const HIERARCHY: Record<string, string[]> = {
  ROLE_SUPER_ADMIN: ['ROLE_ADMIN'],
  ROLE_ADMIN: ['ROLE_CONTRIBUTEUR'],
  ROLE_CONTRIBUTEUR: ['ROLE_USER'],
};

/**
 * Rôles atteignables depuis les rôles de l'utilisateur
 * @param roles
 */
function reachableRoles(roles: string[]): Set<string> {
  const reached = new Set<string>();
  const queue = [...roles];
  while (queue.length > 0) {
    const role = queue.shift() as string;
    if (!reached.has(role)) {
      reached.add(role);
      queue.push(...(HIERARCHY[role] ?? []));
    }
  }
  return reached;
}

/**
 * Crée les droits de l'utilisateur
 * @param roles rôles bruts du JSON de démarrage
 * @param menu modules visibles, avec leurs opérations
 */
export function createCan(roles: string[], menu: AdminMenu): AdminCan {
  const reached = reachableRoles(roles);
  const canOperation = (name: string): boolean => menu.modules.value.some((module) => module.operations.includes(name));

  return {
    canOperation,
    can(action: ItemAction, operation: string, item?: ItemWithPermissions | null): boolean {
      return canOperation(operation) && item?.permissions?.[action] !== false;
    },
    hasRole(role: string): boolean {
      return reached.has(role);
    },
  };
}

/**
 * Droits de l'utilisateur, lus dans le contexte de l'application
 */
export function useCan(): AdminCan {
  return useAdminContext().can;
}
