/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Numéros affichés par une pagination : première et dernière page, page courante et ses voisines
 */
import { computed, toValue, type ComputedRef, type MaybeRefOrGetter } from 'vue';

/**
 * @returns numéros triés, null à la place d'une coupure
 */
export function usePaginationNumbers(
  page: MaybeRefOrGetter<number>,
  lastPage: MaybeRefOrGetter<number>
): ComputedRef<(number | null)[]> {
  return computed(() => {
    const current = toValue(page);
    const last = toValue(lastPage);
    const pages = new Set([1, last, current - 1, current, current + 1]);
    const sorted = [...pages].filter((n) => n >= 1 && n <= last).sort((a, b) => a - b);
    return sorted.flatMap((n, i) => (i > 0 && n - sorted[i - 1] > 1 ? [null, n] : [n]));
  });
}
