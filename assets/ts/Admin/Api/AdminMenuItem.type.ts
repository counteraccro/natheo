/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource AdminMenuItem de l'API v2 : entrée du menu du back-office, liste plate
 */
import type { JsonLdResource } from '@/ts/Global/Api/Hydra.type';

export interface AdminMenuItem extends JsonLdResource {
  id: string;
  parent: string | null;
  module: string | null;
  label: string;
  /** Tracé SVG de l'icône */
  icon: string;
  position: number;
  /** Page Twig, rechargée */
  legacyUrl: string | null;
  /** Nom de la route Vue d'un écran de l'application */
  route: string | null;
  /** Chemin de cet écran sous /admin/<langue>/ */
  path: string | null;
  shortcut: boolean;
  hidden: boolean;
  badge: number | null;
}
