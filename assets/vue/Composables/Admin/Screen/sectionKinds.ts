/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types de section des écrans décrits par des données : une icône par type, la même sur tous les écrans
 */
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';

export const SECTION_ICONS = {
  /** Textes par langue */
  label: 'globe',
  /** Couleur, rendu sur le site */
  appearance: 'palette',
  /** Activation, visibilité */
  status: 'eye',
  /** Contenu rédigé */
  content: 'file',
  /** Référencement */
  seo: 'search',
  /** Droits, utilisateurs */
  rights: 'user',
  /** Informations sur l'élément (auteur, dates) */
  info: 'info',
} as const satisfies Record<string, IconName>;

export type SectionKind = keyof typeof SECTION_ICONS;
