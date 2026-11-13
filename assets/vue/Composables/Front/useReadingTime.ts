/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Temps de lecture estimé d'une page, en minutes, d'après le texte de ses blocs chargés
 */
import { computed, type ComputedRef, type Ref } from 'vue';
import type { PageBlock } from '@/vue/Composables/Front/usePageBlocks';
import type { FaqContent, TextContent } from '@/ts/Front/Api/PageContent.type';

const WORDS_PER_MINUTE = 230;

function countWords(html: string): number {
  const text = new DOMParser().parseFromString(html, 'text/html').body.textContent ?? '';
  return text.split(/\s+/).filter((word) => word !== '').length;
}

function blockWords(block: PageBlock): number {
  const content = block.content?.content;
  if (!content) {
    return 0;
  }
  if (block.type === 'text') {
    return countWords((content as TextContent).html);
  }
  if (block.type === 'faq') {
    const faq = content as FaqContent;
    return faq.categories.reduce(
      (total, category) =>
        total + category.questions.reduce((sum, question) => sum + countWords(`${question.title} ${question.answer}`), 0),
      countWords(faq.title)
    );
  }
  return 0;
}

/**
 * @returns minutes arrondies au supérieur, null tant qu'aucun texte n'est chargé
 */
export function useReadingTime(blocks: Ref<PageBlock[]>): ComputedRef<number | null> {
  return computed(() => {
    const words = blocks.value.reduce((total, block) => total + blockWords(block), 0);
    return words === 0 ? null : Math.ceil(words / WORDS_PER_MINUTE);
  });
}
