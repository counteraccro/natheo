<script setup lang="ts" generic="T extends DataTableRow">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Grille de données : tri, recherche en deux temps (page affichée puis base), colonnes réglables, requête SQL,
 * actions par ligne déjà filtrées par l'appelant, sélection multiple et actions groupées, squelette, état vide et
 * pagination ; liste empilée sous 768 px
 */
import { computed, nextTick, onBeforeUnmount, ref, useId, watch } from 'vue';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import UiButton from '../Form/UiButton.vue';
import UiCheckbox from '../Form/UiCheckbox.vue';
import UiDropdown from '../Overlay/UiDropdown.vue';
import UiDropdownItem from '../Overlay/UiDropdownItem.vue';
import UiDropdownLabel from '../Overlay/UiDropdownLabel.vue';
import UiDropdownSeparator from '../Overlay/UiDropdownSeparator.vue';
import UiTooltip from '../Overlay/UiTooltip.vue';
import UiEmptyState from '../Feedback/UiEmptyState.vue';
import UiSkeleton from '../Feedback/UiSkeleton.vue';
import UiPagination from './UiPagination.vue';
import UiSqlBlock from './UiSqlBlock.vue';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { CONTROL_CLASSES } from '@/vue/Composables/Admin/Ui/controlClasses';
import { cellText, filterRows, highlight } from '@/vue/Composables/Admin/Ui/tableSearch';
import type {
  DataTableAction,
  DataTableColumn,
  DataTableRow,
  DataTableSearchMode,
  DataTableSort,
} from '@/ts/Admin/Ui/Data/UiDataTable.type';

/** Saisie minimale et délai avant la recherche en base quand la page affichée ne contient rien */
const REMOTE_MIN_LENGTH = 2;
const REMOTE_DELAY = 450;
const DIRECT_ACTIONS = 2;

const props = withDefaults(
  defineProps<{
    /** Nom accessible du tableau */
    label: string;
    columns: DataTableColumn[];
    rows: T[];
    rowKey?: string;
    totalItems: number;
    loading?: boolean;
    actionsFor?: (row: T) => DataTableAction[];
    searchable?: boolean;
    /** Requête de la grille, montrée au super administrateur */
    sql?: string | null;
    /** Cases de sélection et barre des actions groupées */
    selectable?: boolean;
    bulkActions?: DataTableAction[];
    /** Libellé d'une ligne pour sa case (première colonne par défaut) */
    rowLabel?: (row: T) => string;
  }>(),
  { rowKey: 'id', searchable: true, sql: null }
);

const sort = defineModel<DataTableSort | null>('sort', { default: null });
const page = defineModel<number>('page', { default: 1 });
const itemsPerPage = defineModel<number>('itemsPerPage', { default: 20 });
/** Clés des lignes sélectionnées, conservées d'une page à l'autre */
const selected = defineModel<(string | number)[]>('selected', { default: () => [] });

const emit = defineEmits<{
  action: [payload: { id: string; row: T }];
  search: [term: string];
  saveSql: [sql: string];
  bulkAction: [payload: { id: string; keys: (string | number)[] }];
  /** Toute la collection : l'appelant demande ses clés à l'API */
  selectAll: [];
}>();

defineSlots<
  {
    filters?: () => unknown;
    toolbar?: () => unknown;
    options?: () => unknown;
    empty?: () => unknown;
  } & Record<`cell-${string}`, (props: { row: T; value: unknown; term: string }) => unknown>
>();

const labels = useUiLabels();
const searchId = `ui-table-search-${useId()}`;

const visible = ref(new Set(props.columns.filter((column) => !column.hidden).map((column) => column.key)));
const toggleable = computed(() => props.columns.filter((column) => column.hidden !== undefined));
const shownColumns = computed(() => props.columns.filter((column) => visible.value.has(column.key)));

const toggleColumn = (key: string): void => {
  const next = new Set(visible.value);
  if (next.has(key)) next.delete(key);
  else next.add(key);
  visible.value = next;
};

const sqlVisible = ref(false);

// Recherche
const term = ref('');
const mode = ref<DataTableSearchMode>('idle');
let remoteTerm: string | null = null;
let timer: ReturnType<typeof setTimeout> | undefined;

const searchRemote = (value: string): void => {
  clearTimeout(timer);
  if (!value) return;
  mode.value = 'pending';
  remoteTerm = value;
  emit('search', value);
};

const onSearchInput = (): void => {
  clearTimeout(timer);
  const value = term.value.trim();
  if (!value) {
    mode.value = 'idle';
    if (remoteTerm !== null) {
      remoteTerm = null;
      emit('search', '');
    }
    return;
  }
  mode.value = 'local';
  if (value.length >= REMOTE_MIN_LENGTH && filterRows(props.rows, shownColumns.value, value).length === 0) {
    timer = setTimeout(() => searchRemote(value), REMOTE_DELAY);
  }
};

const clearSearch = (): void => {
  term.value = '';
  onSearchInput();
};

watch(
  () => props.rows,
  () => {
    if (mode.value === 'pending') mode.value = 'remote';
  }
);
onBeforeUnmount(() => clearTimeout(timer));

const highlightTerm = computed(() => (mode.value === 'local' || mode.value === 'remote' ? term.value.trim() : ''));
const displayed = computed(() =>
  mode.value === 'local' ? filterRows(props.rows, shownColumns.value, term.value) : props.rows
);
const busy = computed(() => props.loading || mode.value === 'pending');
const skeletonRows = computed(() => Math.min(itemsPerPage.value, props.rows.length || 5));
const colspan = computed(
  () => shownColumns.value.length + (props.actionsFor ? 1 : 0) + (props.selectable ? 1 : 0)
);

// Tri
const ariaSort = (column: DataTableColumn): 'ascending' | 'descending' | 'none' | undefined => {
  if (!column.sortable) return undefined;
  if (sort.value?.key !== column.key) return 'none';
  return sort.value.direction === 'asc' ? 'ascending' : 'descending';
};

const toggleSort = (column: DataTableColumn): void => {
  const direction = sort.value?.key === column.key && sort.value.direction === 'asc' ? 'desc' : 'asc';
  sort.value = { key: column.key, direction };
};

const sortIcon = (column: DataTableColumn) => {
  const state = ariaSort(column);
  return state === 'ascending' ? 'arrow-up' : state === 'descending' ? 'arrow-down' : 'arrows-sort';
};

// Lignes
const keyOf = (row: T): string | number => row[props.rowKey] as string | number;
const actionsOf = (row: T): DataTableAction[] => props.actionsFor?.(row) ?? [];
const HIDE_BELOW = { md: 'max-md:hidden', lg: 'max-lg:hidden' } as const;
const hideClass = (column: DataTableColumn): string | undefined =>
  column.hideBelow ? HIDE_BELOW[column.hideBelow] : undefined;

const runAction = (action: DataTableAction, row: T): void => emit('action', { id: action.id, row });

// Sélection
const pageCheckbox = ref<HTMLElement | null>(null);
const selectedSet = computed(() => new Set(selected.value));
const pageKeys = computed(() => displayed.value.map(keyOf));
const pageSelected = computed(() => pageKeys.value.filter((key) => selectedSet.value.has(key)).length);
const allPageSelected = computed(() => pageKeys.value.length > 0 && pageSelected.value === pageKeys.value.length);
const labelOf = (row: T): string =>
  props.rowLabel?.(row) ?? cellText(row[props.columns[0]?.key ?? props.rowKey]);
const sortedBulkActions = computed(() =>
  [...(props.bulkActions ?? [])].sort((a, b) => Number(!!a.danger) - Number(!!b.danger))
);

const toggleRow = (row: T, checked: boolean): void => {
  const key = keyOf(row);
  selected.value = checked ? [...selected.value, key] : selected.value.filter((k) => k !== key);
};

const togglePage = (checked: boolean): void => {
  const keys = new Set(pageKeys.value);
  const others = selected.value.filter((key) => !keys.has(key));
  selected.value = checked ? [...others, ...pageKeys.value] : others;
};

const clearSelection = async (): Promise<void> => {
  selected.value = [];
  await nextTick();
  pageCheckbox.value?.querySelector('input')?.focus();
};
</script>

<template>
  <div class="overflow-hidden rounded-lg border border-line bg-bg shadow-panel">
    <div class="flex flex-wrap items-center gap-2.5 border-b border-line px-4 py-3.5">
      <div v-if="searchable" class="relative flex-[0_1_320px] max-md:flex-[1_1_100%]">
        <label :for="searchId" class="sr-only">{{ labels.dataTable.searchLabel }}</label>
        <UiIcon
          name="search"
          class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-fg-subtle"
        />
        <input
          :id="searchId"
          v-model="term"
          type="search"
          autocomplete="off"
          :placeholder="labels.dataTable.searchPlaceholder"
          :class="[CONTROL_CLASSES, 'h-9 pr-3 pl-9']"
          @input="onSearchInput"
          @keydown.enter.prevent="searchRemote(term.trim())"
          @keydown.esc.prevent="clearSearch"
        />
      </div>
      <slot name="filters" />
      <div class="ml-auto flex items-center gap-2">
        <slot name="toolbar" />
        <UiDropdown
          v-if="toggleable.length || sql || $slots.options"
          :label="labels.dataTable.options"
          icon="sliders"
          size="sm"
          width="min-w-60"
        >
          <template v-if="toggleable.length">
            <UiDropdownLabel>{{ labels.dataTable.columns }}</UiDropdownLabel>
            <UiDropdownItem
              v-for="column in toggleable"
              :key="column.key"
              type="checkbox"
              :checked="visible.has(column.key)"
              @select="toggleColumn(column.key)"
            >
              {{ column.label }}
            </UiDropdownItem>
          </template>
          <template v-if="$slots.options">
            <UiDropdownSeparator v-if="toggleable.length" />
            <slot name="options" />
          </template>
          <template v-if="sql">
            <UiDropdownSeparator v-if="toggleable.length || $slots.options" />
            <UiDropdownItem type="checkbox" :checked="sqlVisible" @select="sqlVisible = !sqlVisible">
              {{ labels.dataTable.sqlShow }}
            </UiDropdownItem>
          </template>
        </UiDropdown>
      </div>
    </div>

    <div aria-live="polite">
      <div
        v-if="mode === 'local'"
        class="flex flex-wrap items-center gap-x-2.5 gap-y-2 border-b border-line px-4 py-2 text-sm text-fg-muted"
      >
        <UiIcon name="check-circle" class="size-4 text-success" />
        <span>{{ labels.dataTable.searchLocal(displayed.length, rows.length) }}</span>
        <span class="ml-auto text-xs text-fg-subtle max-md:hidden">{{ labels.dataTable.searchHint }}</span>
      </div>
      <div
        v-else-if="mode === 'pending'"
        class="flex items-center gap-2.5 border-b border-line bg-primary-soft px-4 py-2 text-sm text-on-primary-soft"
      >
        <span class="size-3.5 animate-[ui-spin_.7s_linear_infinite] rounded-full border-2 border-current border-r-transparent" />
        <span>{{ labels.dataTable.searchPending }}</span>
      </div>
      <div
        v-else-if="mode === 'remote'"
        class="flex flex-wrap items-center gap-2.5 border-b border-line bg-primary-soft px-4 py-2 text-sm text-on-primary-soft"
      >
        <UiIcon name="database" class="size-4" />
        <span>{{ labels.dataTable.searchRemote(totalItems) }}</span>
        <button
          type="button"
          class="ui-ring ml-auto cursor-pointer rounded-sm border-0 bg-transparent px-1 py-0.5 font-[650] text-inherit underline underline-offset-3"
          @click="clearSearch"
        >
          {{ labels.dataTable.searchBack }}
        </button>
      </div>
    </div>

    <div
      v-if="selectable && selected.length"
      role="region"
      :aria-label="labels.dataTable.selectionRegion"
      class="flex flex-wrap items-center gap-x-3 gap-y-2 border-b border-line bg-primary-soft px-4 py-2 text-sm text-on-primary-soft max-md:hidden"
    >
      <b class="font-[650]" aria-live="polite">{{ labels.dataTable.selected(selected.length) }}</b>
      <button
        v-if="allPageSelected && totalItems > selected.length"
        type="button"
        class="ui-ring cursor-pointer rounded-sm border-0 bg-transparent px-1 py-0.5 font-semibold text-inherit underline underline-offset-3"
        @click="emit('selectAll')"
      >
        {{ labels.dataTable.selectAll(totalItems) }}
      </button>
      <button
        type="button"
        class="ui-ring cursor-pointer rounded-sm border-0 bg-transparent px-1 py-0.5 font-semibold text-inherit underline underline-offset-3"
        @click="clearSelection"
      >
        {{ labels.dataTable.clearSelection }}
      </button>
      <div v-if="sortedBulkActions.length" class="ml-auto flex gap-2">
        <UiButton
          v-for="action in sortedBulkActions"
          :key="action.id"
          size="sm"
          :variant="action.danger ? 'danger-soft' : 'secondary'"
          :icon="action.icon"
          @click="emit('bulkAction', { id: action.id, keys: [...selected] })"
        >
          {{ action.label }}
        </UiButton>
      </div>
    </div>

    <UiSqlBlock v-if="sql && sqlVisible" :sql="sql" @save="emit('saveSql', sql)" @hide="sqlVisible = false" />

    <div class="overflow-x-auto" :aria-busy="busy || undefined">
      <table :aria-label="label" class="w-full border-separate border-spacing-0 tabular-nums max-md:block">
        <thead class="max-md:hidden">
          <tr>
            <th
              v-if="selectable"
              ref="pageCheckbox"
              scope="col"
              class="h-10 w-px border-b border-line bg-[color-mix(in_srgb,var(--surface)_40%,var(--bg))] pr-1 pl-4"
            >
              <UiCheckbox
                :model-value="allPageSelected"
                :indeterminate="pageSelected > 0 && !allPageSelected"
                :aria-label="labels.dataTable.selectPage"
                :disabled="busy || pageKeys.length === 0"
                @update:model-value="togglePage"
              />
            </th>
            <th
              v-for="column in shownColumns"
              :key="column.key"
              scope="col"
              :aria-sort="ariaSort(column)"
              class="h-10 border-b border-line bg-[color-mix(in_srgb,var(--surface)_40%,var(--bg))] px-4 text-left text-[12.5px] font-semibold whitespace-nowrap text-fg-subtle"
              :class="[column.align === 'end' && 'text-right', hideClass(column)]"
            >
              <button
                v-if="column.sortable"
                type="button"
                class="ui-ring -mx-1.5 inline-flex cursor-pointer items-center gap-1 rounded-sm border-0 bg-transparent px-1.5 py-1 font-semibold text-inherit hover:bg-[color-mix(in_srgb,var(--fg)_5%,transparent)] hover:text-fg"
                :class="ariaSort(column) !== 'none' && 'text-fg'"
                @click="toggleSort(column)"
              >
                {{ column.label }}
                <UiIcon
                  :name="sortIcon(column)"
                  class="size-4"
                  :class="ariaSort(column) === 'none' ? 'opacity-55' : 'text-primary'"
                />
              </button>
              <template v-else>{{ column.label }}</template>
            </th>
            <th
              v-if="actionsFor"
              scope="col"
              class="h-10 w-px border-b border-line bg-[color-mix(in_srgb,var(--surface)_40%,var(--bg))] px-4"
            >
              <span class="sr-only">{{ labels.dataTable.actions }}</span>
            </th>
          </tr>
        </thead>
        <tbody class="max-md:block">
          <template v-if="busy">
            <tr v-for="n in skeletonRows" :key="`skeleton-${n}`" class="max-md:block">
              <td :colspan="colspan" class="h-14 border-b border-line px-4 max-md:block max-md:py-4">
                <UiSkeleton variant="row" :columns="Math.max(shownColumns.length, 2)" />
              </td>
            </tr>
          </template>
          <tr v-else-if="displayed.length === 0" class="max-md:block">
            <td :colspan="colspan" class="max-md:block">
              <slot name="empty">
                <UiEmptyState :title="labels.dataTable.emptyTitle" :text="labels.dataTable.emptyText" />
              </slot>
            </td>
          </tr>
          <template v-else>
            <tr
              v-for="row in displayed"
              :key="keyOf(row)"
              :class="selectable && selectedSet.has(keyOf(row)) && 'bg-[color-mix(in_srgb,var(--primary-soft)_70%,transparent)] hover:bg-primary-soft'"
              class="group/row transition-colors duration-100 hover:bg-[color-mix(in_srgb,var(--surface)_55%,transparent)] has-[[aria-expanded=true]]:bg-[color-mix(in_srgb,var(--primary-soft)_55%,transparent)] max-md:grid max-md:grid-cols-[minmax(0,1fr)_auto] max-md:gap-x-3 max-md:gap-y-1.5 max-md:border-b max-md:border-line max-md:px-4 max-md:py-3.5"
            >
              <td
                v-if="selectable"
                class="h-14 w-px border-b border-line pr-1 pl-4 align-middle group-last/row:border-b-0 max-md:hidden"
              >
                <UiCheckbox
                  :model-value="selectedSet.has(keyOf(row))"
                  :aria-label="labels.dataTable.selectRow(labelOf(row))"
                  @update:model-value="toggleRow(row, $event)"
                />
              </td>
              <td
                v-for="(column, index) in shownColumns"
                :key="column.key"
                :data-label="column.label"
                class="h-14 border-b border-line px-4 py-1.5 align-middle group-last/row:border-b-0 max-md:col-start-1 max-md:flex max-md:h-auto max-md:items-center max-md:gap-2 max-md:border-0 max-md:p-0"
                :class="[
                  column.align === 'end' && 'text-right',
                  hideClass(column),
                  index > 0 &&
                    'max-md:before:w-[92px] max-md:before:shrink-0 max-md:before:text-sm max-md:before:text-fg-subtle max-md:before:content-[attr(data-label)]',
                ]"
              >
                <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]" :term="highlightTerm">
                  <template v-for="(segment, i) in highlight(cellText(row[column.key]), highlightTerm)" :key="i">
                    <mark
                      v-if="segment.match"
                      class="rounded-[3px] bg-[color-mix(in_srgb,var(--primary)_30%,transparent)] px-px text-inherit"
                      >{{ segment.text }}</mark
                    ><template v-else>{{ segment.text }}</template>
                  </template>
                </slot>
              </td>
              <td
                v-if="actionsFor"
                class="h-14 w-px border-b border-line px-4 text-right whitespace-nowrap group-last/row:border-b-0 max-md:col-start-2 max-md:row-start-1 max-md:h-auto max-md:border-0 max-md:p-0"
              >
                <div v-if="actionsOf(row).length" class="inline-flex items-center gap-0.5">
                  <span
                    v-for="action in actionsOf(row).slice(0, DIRECT_ACTIONS)"
                    :key="action.id"
                    class="max-md:hidden"
                  >
                    <UiTooltip :text="action.label">
                      <UiButton
                        variant="ghost"
                        size="sm"
                        :icon="action.icon"
                        :aria-label="action.label"
                        :class="action.danger ? 'text-danger' : 'text-fg-subtle hover:text-fg'"
                        @click="runAction(action, row)"
                      />
                    </UiTooltip>
                  </span>
                  <span v-if="actionsOf(row).length > DIRECT_ACTIONS" class="max-md:hidden">
                    <UiDropdown :label="labels.dataTable.moreActions" icon="dots" icon-only variant="ghost" size="sm">
                      <UiDropdownItem
                        v-for="action in actionsOf(row).slice(DIRECT_ACTIONS)"
                        :key="action.id"
                        :icon="action.icon"
                        :danger="action.danger"
                        @select="runAction(action, row)"
                      >
                        {{ action.label }}
                      </UiDropdownItem>
                    </UiDropdown>
                  </span>
                  <span class="md:hidden">
                    <UiDropdown :label="labels.dataTable.moreActions" icon="dots" icon-only variant="ghost" size="sm">
                      <UiDropdownItem
                        v-for="action in actionsOf(row)"
                        :key="action.id"
                        :icon="action.icon"
                        :danger="action.danger"
                        @select="runAction(action, row)"
                      >
                        {{ action.label }}
                      </UiDropdownItem>
                    </UiDropdown>
                  </span>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div v-if="totalItems > 0 && mode !== 'pending'" class="border-t border-line px-4 py-3">
      <UiPagination v-model:page="page" v-model:items-per-page="itemsPerPage" :total-items="totalItems" />
    </div>
  </div>
</template>
