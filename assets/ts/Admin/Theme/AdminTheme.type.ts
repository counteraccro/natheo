/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Contrat d'un thème du back-office : layout autour des écrans et page introuvable
 */
import type { Component } from 'vue';
import type { AdminBootstrap } from '@/ts/Admin/App/AdminBootstrap.type';
import type { SidebarItem } from '@/ts/Admin/Layout/AdminSidebar.type';
import type { HeaderSection, HeaderShortcut } from '@/ts/Admin/Layout/AdminHeader.type';

/** Composant chargé tout de suite ou à la demande */
export type ThemeComponent = Component | (() => Promise<Component>);

/** Props du layout, calculées par le socle ; l'écran courant arrive dans son slot par défaut */
export interface AdminLayoutProps {
  items: SidebarItem[];
  section: HeaderSection | null;
  shortcuts: HeaderShortcut[];
  bootstrap: AdminBootstrap;
}

/** Props de la page introuvable */
export interface AdminNotFoundProps {
  /** Adresse demandée, depuis la racine du site */
  path: string;
  dashboardUrl: string;
}

export interface AdminTheme {
  /** Identifiant du thème (natheo_studio) */
  id: string;
  /** Reçoit AdminLayoutProps, toujours affiché */
  layout: Component;
  /** Reçoit AdminNotFoundProps, affiché dans le layout */
  notFound: ThemeComponent;
}
