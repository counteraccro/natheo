/**
 * @author Gourdon Aymeric
 * @version 1.0
 * État d'un chargement asynchrone (données, chargement en cours, erreur), utilisé par les composables du site public
 */
import { ref, shallowRef, type Ref, type ShallowRef } from 'vue';

export interface AsyncState<T> {
  data: ShallowRef<T | null>;
  loading: Ref<boolean>;
  error: ShallowRef<unknown>;
  run(loader: () => Promise<T>): Promise<T | null>;
}

export function useAsync<T>(): AsyncState<T> {
  const data = shallowRef<T | null>(null);
  const loading = ref(false);
  const error = shallowRef<unknown>(null);

  async function run(loader: () => Promise<T>): Promise<T | null> {
    loading.value = true;
    error.value = null;
    try {
      data.value = await loader();
    } catch (e) {
      error.value = e;
    } finally {
      loading.value = false;
    }
    return data.value;
  }

  return { data, loading, error, run };
}
