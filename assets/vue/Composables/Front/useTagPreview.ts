/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Aperçu d'un tag au design du thème, affiché dans une iframe du formulaire des tags
 */
import { computed, type ComputedRef } from 'vue';
import { usePreviewFrame } from '@/vue/Composables/Front/usePreviewFrame';
import type { Tag } from '@/ts/Front/Api/Page.type';

/**
 * Tag reçu du formulaire, en liste pour le composant des tags du thème (vide avant le premier envoi)
 */
export function useTagPreview(): ComputedRef<Tag[]> {
  const tag = usePreviewFrame<Tag>();
  return computed(() => (tag.value === null ? [] : [tag.value]));
}
