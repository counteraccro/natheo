/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Lien « Modifier cette page » vers le back-office, null sans droit de modification sur la page affichée
 * ou quand la page affichée n'est plus celle demandée au serveur
 */
import { computed, type ComputedRef } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';

export function useEditLink(): ComputedRef<string | null> {
  const context = useFrontContext();
  return computed(() => (context.slug.value === context.datas.slug ? (context.datas.user?.editUrl ?? null) : null));
}
