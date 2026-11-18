<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Pagination : plage affichée annoncée, nombre de lignes par page, page précédente, numéros et page suivante
 */
import { computed, useId } from 'vue';
import UiButton from '../Form/UiButton.vue';
import UiSelect from '../Form/UiSelect.vue';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { usePaginationNumbers } from '@/vue/Composables/Front/usePaginationNumbers';

const props = withDefaults(defineProps<{ totalItems: number; perPageOptions?: number[] }>(), {
  perPageOptions: () => [10, 20, 50, 100],
});
const page = defineModel<number>('page', { default: 1 });
const itemsPerPage = defineModel<number>('itemsPerPage', { default: 20 });

const labels = useUiLabels();
const perPageId = `ui-per-page-${useId()}`;
const lastPage = computed(() => Math.max(1, Math.ceil(props.totalItems / itemsPerPage.value)));
const numbers = usePaginationNumbers(page, lastPage);
const from = computed(() => (props.totalItems === 0 ? 0 : (page.value - 1) * itemsPerPage.value + 1));
const to = computed(() => Math.min(page.value * itemsPerPage.value, props.totalItems));
// La valeur courante reste proposée même hors de la liste
const options = computed(() =>
  [...new Set([...props.perPageOptions, itemsPerPage.value])]
    .sort((a, b) => a - b)
    .map((value) => ({ value, label: String(value) }))
);

const changePerPage = (value: string | number | null | undefined): void => {
  itemsPerPage.value = Number(value);
  page.value = 1;
};

const PAGE_CLASSES =
  'ui-ring grid h-8 min-w-8 cursor-pointer place-items-center rounded-sm border border-transparent bg-transparent px-2 text-fg-muted tabular-nums hover:bg-surface hover:text-fg aria-[current=page]:border-line-strong aria-[current=page]:bg-bg aria-[current=page]:font-bold aria-[current=page]:text-fg aria-[current=page]:shadow-[0_1px_2px_rgb(17_17_20/.06)]';
</script>

<template>
  <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-fg-subtle">
    <p class="m-0 tabular-nums" aria-live="polite">{{ labels.pagination.range(from, to, totalItems) }}</p>
    <div class="flex flex-wrap items-center gap-3">
      <span class="flex items-center gap-2">
        <label :for="perPageId">{{ labels.pagination.itemsPerPage }}</label>
        <span class="w-[72px]">
          <UiSelect
            :id="perPageId"
            :model-value="itemsPerPage"
            :options="options"
            size="sm"
            @update:model-value="changePerPage"
          />
        </span>
      </span>
      <nav :aria-label="labels.pagination.nav" class="flex items-center gap-1">
        <UiButton
          variant="ghost"
          size="sm"
          icon="chevron-left"
          :aria-label="labels.pagination.previous"
          :disabled="page <= 1"
          @click="page = page - 1"
        />
        <template v-for="(n, i) in numbers" :key="n ?? `gap-${i}`">
          <span v-if="n === null" class="px-1" aria-hidden="true">…</span>
          <button
            v-else
            type="button"
            :class="PAGE_CLASSES"
            :aria-current="n === page ? 'page' : undefined"
            :aria-label="labels.pagination.page(n)"
            @click="page = n"
          >
            {{ n }}
          </button>
        </template>
        <UiButton
          variant="ghost"
          size="sm"
          icon="chevron-right"
          :aria-label="labels.pagination.next"
          :disabled="page >= lastPage"
          @click="page = page + 1"
        />
      </nav>
    </div>
  </div>
</template>
