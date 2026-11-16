/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Options publiques du site (nom, pied de page, réseaux sociaux, commentaires ouverts)
 */
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import { useAsync, type AsyncState } from '@/vue/Composables/Front/useAsync';
import type { OptionSystem, SiteOptions } from '@/ts/Front/Api/OptionSystem.type';

export function useSiteOptions(): AsyncState<SiteOptions> {
  const context = useFrontContext();
  const state = useAsync<SiteOptions>();

  state.run(() =>
    context.once('site_options', async () => {
      const options = await context.api.getCollection<OptionSystem>('/option_systems');
      return Object.fromEntries(options.members.map((option) => [option.key, option.value])) as unknown as SiteOptions;
    })
  );
  return state;
}
