import { describe, expect, it } from 'vitest';
import { ref } from 'vue';
import { usePaginationNumbers } from '@/vue/Composables/Front/usePaginationNumbers';

describe('usePaginationNumbers', () => {
  it('affiche toutes les pages quand il y en a peu', () => {
    expect(usePaginationNumbers(2, 3).value).toEqual([1, 2, 3]);
  });

  it('remplace les pages sautées par null', () => {
    expect(usePaginationNumbers(5, 10).value).toEqual([1, null, 4, 5, 6, null, 10]);
  });

  it('suit la page courante', () => {
    const page = ref(1);
    const numbers = usePaginationNumbers(page, 10);
    expect(numbers.value).toEqual([1, 2, null, 10]);
    page.value = 10;
    expect(numbers.value).toEqual([1, null, 9, 10]);
  });
});
