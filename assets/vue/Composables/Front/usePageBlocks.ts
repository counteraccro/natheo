/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Blocs d'une page : disposition selon le rendu, contenu chargé et composant du thème selon le type
 */
import { computed, shallowRef, watch, type Component, type ComputedRef, type Ref, type ShallowRef } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import type { Page, PageRender } from '@/ts/Front/Api/Page.type';
import type { PageContent, PageContentType } from '@/ts/Front/Api/PageContent.type';

/** Largeur d'un bloc dans la disposition, traduite en classes par le thème */
export type BlockSize = 'full' | 'half' | 'third';

/** Largeur de chaque bloc, dans l'ordre des contenus, pour chaque rendu */
const LAYOUTS: Record<PageRender, BlockSize[]> = {
  one_block: ['full'],
  two_block: ['half', 'half'],
  three_block: ['third', 'third', 'third'],
  two_block_bottom: ['full', 'full'],
  three_block_bottom: ['full', 'full', 'full'],
  one_two_block: ['full', 'half', 'half'],
  two_one_block: ['half', 'half', 'full'],
  two_two_block: ['half', 'half', 'half', 'half'],
};

export type BlockComponents = Partial<Record<PageContentType, Component>>;

export interface PageBlock {
  id: number;
  type: PageContentType;
  size: BlockSize;
  component: Component;
  /** Contenu complet, null tant qu'il n'est pas chargé ou en cas d'erreur */
  content: PageContent | null;
  error: unknown;
}

export interface PageBlocks {
  blocks: ComputedRef<PageBlock[]>;
  loading: Ref<boolean>;
}

export function usePageBlocks(page: Ref<Page | null>, components: BlockComponents): PageBlocks {
  const context = useFrontContext();
  const contents: ShallowRef<Record<number, { content: PageContent | null; error: unknown }>> = shallowRef({});
  const loading = shallowRef(false);

  const layout = computed(() => {
    if (page.value === null) {
      return [];
    }
    const sizes = LAYOUTS[page.value.render] ?? LAYOUTS.one_block;
    const sorted = [...page.value.contents].sort((a, b) => a.position - b.position);
    return sizes.flatMap((size, index) => {
      const summary = sorted[index];
      const component = summary ? components[summary.type] : undefined;
      return summary && component ? [{ summary, size, component }] : [];
    });
  });

  watch(
    layout,
    async (items) => {
      loading.value = true;
      const previews = context.datas.preview?.contents ?? [];
      const loaded = await Promise.all(
        items.map(async ({ summary }) => {
          const preview = previews.find((content) => content.id === summary.id);
          if (preview !== undefined) {
            return [summary.id, { content: preview, error: null }] as const;
          }
          try {
            return [summary.id, { content: await context.api.get<PageContent>(summary['@id']), error: null }] as const;
          } catch (error) {
            return [summary.id, { content: null, error }] as const;
          }
        })
      );
      contents.value = Object.fromEntries(loaded);
      loading.value = false;
    },
    { immediate: true }
  );

  const blocks = computed(() =>
    layout.value.map(({ summary, size, component }) => ({
      id: summary.id,
      type: summary.type,
      size,
      component,
      content: contents.value[summary.id]?.content ?? null,
      error: contents.value[summary.id]?.error ?? null,
    }))
  );

  return { blocks, loading };
}
