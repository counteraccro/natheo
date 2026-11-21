/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types communs des écrans décrits par des données
 */
import type { Component } from 'vue';
import type { ItemWithPermissions } from '@/vue/Composables/Admin/useCan';

/** Composant d'un module, chargé à la demande */
export type ScreenComponent = Component | (() => Promise<Component>);

/** Élément d'une ressource d'administration */
export interface ScreenItem extends ItemWithPermissions {
  id: number | string;
  disabled?: boolean;
}

/** Auteur d'un contenu (AdminAuthor) */
export interface ScreenAuthor {
  id: number;
  login: string;
}
