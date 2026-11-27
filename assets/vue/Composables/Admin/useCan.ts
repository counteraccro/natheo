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
  /** Le rôle est donné à l'utilisateur, directement ou par la hiérarchie des rôles du serveur */
  hasRole(role: string): boolean;
}

/**
 * Crée les droits de l'utilisateur
 * @param roles rôles atteignables, donnés par le JSON de démarrage
 * @param menu modules visibles, avec leurs opérations
 */
export function createCan(roles: string[], menu: AdminMenu): AdminCan {
  const reached = new Set(roles);
  const canOperation = (name: string): boolean => menu.hasOperation(name);

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
