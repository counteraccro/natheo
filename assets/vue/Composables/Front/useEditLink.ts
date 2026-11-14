/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lien « Modifier cette page » vers le back-office, null sans droit de modification sur la page affichée
 */
import { computed, type ComputedRef } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';

export function useEditLink(): ComputedRef<string | null> {
  const context = useFrontContext();
  return computed(() => context.datas.user?.editUrl ?? null);
}
