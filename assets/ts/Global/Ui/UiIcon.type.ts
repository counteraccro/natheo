/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types de UiIcon et de son registre
 */
export type { IconName } from '@/vue/Composables/Global/icons';

export interface IconDefinition {
  /** Rempli en currentColor ; sinon dessiné au trait */
  solid?: boolean;
  /** Règle de remplissage evenodd (icônes pleines avec des trous) */
  evenodd?: boolean;
  paths: readonly string[];
}
