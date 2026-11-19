/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types de AdminSidebar, indépendants de l'API
 */
export interface SidebarItem {
  id: string;
  label: string;
  /** Tracé SVG reçu de l'API */
  icon?: string;
  /** Absent : dossier */
  href?: string;
  active?: boolean;
  badge?: number;
  children?: SidebarItem[];
}

export interface SidebarUser {
  name: string;
  /** Rôle ou adresse affichés sous le nom */
  detail?: string;
  initials: string;
}
