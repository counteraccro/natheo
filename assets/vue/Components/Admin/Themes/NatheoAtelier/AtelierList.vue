<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Liste de l'établi Natheo-Atelier, gardée à gauche de l'élément ouvert : recherche en deux temps, filtres en
 * pastilles, tri, sélection et actions groupées, lignes compactes avec leurs actions, requête SQL en tête du volet
 */
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { ATELIER_PANE_TOP, NARROW_QUERY } from '@admin/Themes/NatheoAtelier/atelier';
import { useRoute, useRouter } from 'vue-router';
import {
  UiButton,
  UiDropdown,
  UiDropdownItem,
  UiDropdownSeparator,
  UiIcon,
  UiPagination,
  UiSqlBlock,
  useTableSearch,
  useUiLabels,
  type DataTableRow,
} from '@ui';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { useCollectionScreen } from '@/vue/Composables/Admin/Screen/useCollectionScreen';
import { toComponent } from '@/vue/Composables/Admin/Screen/adminTheme';
import type { CollectionScreenDescriptor } from '@/ts/Admin/Screen/CollectionScreen.type';
import type { ScreenItem } from '@/ts/Admin/Screen/Screen.type';

const props = defineProps<{ descriptor: CollectionScreenDescriptor }>();

const { t } = useAdminContext();
const labels = useUiLabels();
const router = useRouter();
const route = useRoute();
const screen = useCollectionScreen(props.descriptor);
const { items, total, page, itemsPerPage, sort, search, selected, sql, loading } = screen.collection;
const { status, author } = screen.filter;
const descriptor = props.descriptor;

const title = t(descriptor.title);
const sqlVisible = ref(false);
const media = window.matchMedia(NARROW_QUERY);
const narrow = ref(media.matches);
const onMedia = (event: MediaQueryListEvent): void => {
  narrow.value = event.matches;
};
media.addEventListener('change', onMedia);
onBeforeUnmount(() => media.removeEventListener('change', onMedia));
const tableSearch = useTableSearch<DataTableRow>({
  rows: () => items.value as unknown as DataTableRow[],
  columns: () => screen.columns,
  loading: () => loading.value,
  search: (value) => (search.value = value),
  resetPage: () => (page.value = 1),
});
const { term } = tableSearch;
const rows = computed(() => tableSearch.displayed.value as unknown as ScreenItem[]);

// Colonne du libellé : la pastille, sinon le premier texte ; l'identifiant est montré en fin de ligne
const badge = descriptor.columns.find((column) => column.type === 'badge');
const labelColumn =
  badge ?? descriptor.columns.find((column) => (column.type ?? 'text') === 'text' && column.key !== 'id');
const metaColumns = descriptor.columns.filter(
  (column) => !column.hidden && column !== labelColumn && column.key !== 'id'
);
const components = new Map(
  metaColumns.filter((column) => column.component).map((column) => [column.key, toComponent(column.component!)])
);
const sortable = screen.columns.filter((column) => column.sortable);
const editAction = descriptor.actions?.find((action) => action.type === 'edit');
const currentId = computed(() => (typeof route.params.id === 'string' ? route.params.id : null));
const selectable = screen.bulkActions.length > 0;

const fieldOf = (row: ScreenItem, key: string): unknown => (row as unknown as Record<string, unknown>)[key];
const hrefOf = (row: ScreenItem): string | undefined =>
  editAction ? router.resolve({ name: editAction.route, params: { id: String(row.id) } }).href : undefined;
const colorOf = (row: ScreenItem): string => (badge?.color ? String(fieldOf(row, badge.color) ?? '') : '');
const rowActions = (row: ScreenItem) => screen.actionsFor(row).filter((action) => action.id !== 'edit' && action.id !== 'view');
const isSelected = (row: ScreenItem): boolean => selected.value.includes(row.id);
const toggle = (row: ScreenItem): void => {
  selected.value = isSelected(row) ? selected.value.filter((id) => id !== row.id) : [...selected.value, row.id];
};

const sortLabel = computed(() => {
  const column = sortable.find((candidate) => candidate.key === sort.value?.key);
  return column ? column.label : t('admin_app.atelier.sort');
});
/**
 * Tri par une colonne, second choix de la même colonne pour inverser l'ordre
 * @param key
 */
const sortBy = (key: string): void => {
  sort.value =
    sort.value?.key === key
      ? { key, direction: sort.value.direction === 'asc' ? 'desc' : 'asc' }
      : { key, direction: 'asc' };
};

onMounted(() => void screen.collection.load());
</script>

<template>
  <section class="flex min-h-0 flex-col border-r border-line bg-bench" :aria-label="title">
    <div class="flex items-center gap-2 pt-4 pr-3.5 pb-2.5 pl-[18px]">
      <h2 class="m-0 flex-1 text-[15px] font-[650]">
        {{ title }} <span class="font-medium text-fg-subtle">{{ total }}</span>
      </h2>
      <UiButton v-if="screen.canCreate && descriptor.create" size="sm" icon="plus" :href="screen.createHref">
        {{ t(descriptor.create.label) }}
      </UiButton>
    </div>

    <div class="flex gap-1.5 pr-3.5 pb-2.5 pl-[18px]">
      <label
        class="flex h-8 min-w-0 flex-1 items-center gap-2 rounded-lg border border-line-strong bg-bg px-2.5 text-fg-subtle focus-within:border-primary"
      >
        <UiIcon name="search" class="size-4 shrink-0" />
        <span class="sr-only">{{ labels.dataTable.searchLabel }}</span>
        <input
          v-model="term"
          type="search"
          :placeholder="labels.dataTable.searchPlaceholder"
          class="min-w-0 flex-1 border-0 bg-transparent text-fg outline-none placeholder:text-fg-subtle"
          @input="tableSearch.onInput()"
          @keydown.enter.prevent="tableSearch.searchRemote()"
        />
      </label>
      <UiDropdown
        :label="labels.dataTable.options"
        icon="sliders"
        icon-only
        variant="ghost"
        size="sm"
        placement="bottom-end"
      >
        <UiDropdownItem v-for="column in sortable" :key="column.key" @select="sortBy(column.key)">
          {{ t('admin_app.atelier.sort_by', { column: column.label }) }}
        </UiDropdownItem>
        <template v-if="selectable">
          <UiDropdownSeparator />
          <UiDropdownItem icon="check" @select="screen.selectAll()">{{
            labels.dataTable.selectAll(total)
          }}</UiDropdownItem>
        </template>
        <template v-if="screen.canSql && sql">
          <UiDropdownSeparator />
          <UiDropdownItem type="checkbox" :checked="sqlVisible" @select="sqlVisible = !sqlVisible">
            {{ labels.dataTable.sqlShow }}
          </UiDropdownItem>
        </template>
      </UiDropdown>
    </div>

    <div v-if="descriptor.filters?.length" class="flex flex-wrap gap-1.5 pr-3.5 pb-2.5 pl-[18px]">
      <template v-if="descriptor.filters.includes('status')">
        <button
          v-for="chip in screen.statusOptions"
          :key="chip.value"
          type="button"
          :aria-pressed="status === chip.value"
          class="atelier-chip"
          @click="status = chip.value"
        >
          {{ chip.label }}
        </button>
      </template>
      <button
        v-if="descriptor.filters.includes('author')"
        type="button"
        :aria-pressed="author === 'me'"
        class="atelier-chip"
        @click="author = author === 'me' ? '' : 'me'"
      >
        {{ screen.authorOptions[1].label }}
      </button>
    </div>

    <div
      v-if="selected.length"
      role="region"
      :aria-label="labels.dataTable.selectionRegion"
      class="mx-3.5 mb-2.5 ml-[18px] flex flex-wrap items-center gap-1.5 rounded-[10px] bg-fg py-1.5 pr-1.5 pl-3 text-[13px] font-semibold text-bg"
    >
      <span class="flex-1">{{ labels.dataTable.selected(selected.length) }}</span>
      <button
        v-for="action in screen.bulkActions"
        :key="action.id"
        type="button"
        class="h-7 cursor-pointer rounded-[7px] border-0 bg-bg/15 px-2.5 text-[12.5px] font-semibold text-inherit"
        @click="screen.runBulk(action.id, selected)"
      >
        {{ action.label }}
      </button>
      <button
        type="button"
        class="grid size-7 cursor-pointer place-items-center rounded-[7px] border-0 bg-transparent text-inherit"
        :aria-label="labels.dataTable.clearSelection"
        @click="selected = []"
      >
        <UiIcon name="close" class="size-4" />
      </button>
    </div>

    <div
      class="flex items-center justify-between gap-2 pt-1 pr-3.5 pb-1.5 pl-[18px] text-[12.5px] text-fg-subtle"
      aria-live="polite"
    >
      <span v-if="tableSearch.mode.value === 'local'">
        {{ labels.dataTable.searchLocal(rows.length, items.length) }}
      </span>
      <span v-else-if="tableSearch.mode.value === 'pending'">{{ labels.dataTable.searchPending }}</span>
      <span v-else-if="tableSearch.mode.value === 'remote'">
        {{ labels.dataTable.searchRemote(total) }}
        <button
          type="button"
          class="ui-ring cursor-pointer rounded-sm border-0 bg-transparent p-0 font-[650] text-fg-muted underline underline-offset-3"
          @click="tableSearch.clear()"
        >
          {{ labels.dataTable.searchBack }}
        </button>
      </span>
      <span v-else>{{ t('admin_app.atelier.count', { count: total }) }}</span>
      <span v-if="sort">{{ sortLabel }} {{ sort.direction === 'asc' ? '↑' : '↓' }}</span>
    </div>

    <!-- Volet de droite plus large que la colonne : la requête s'y affiche, sauf sur écran étroit (volet masqué) -->
    <Teleport defer :to="`#${ATELIER_PANE_TOP}`" :disabled="narrow">
      <UiSqlBlock
        v-if="screen.canSql && sql && sqlVisible"
        :sql="sql"
        :saved="screen.savedSql.value === sql"
        :class="narrow ? 'mx-3.5 mb-2.5 ml-[18px]' : 'mx-10 mt-[26px] -mb-2 max-md:mx-5'"
        @save="screen.saveSql(sql)"
        @hide="sqlVisible = false"
      />
    </Teleport>

    <ul
      class="m-0 min-h-0 flex-1 list-none overflow-auto pt-0 pr-0 pb-3 pl-2"
      :aria-busy="loading || tableSearch.mode.value === 'pending' || undefined"
      :aria-label="title"
    >
      <li v-if="!loading && rows.length === 0" class="px-2.5 py-6 text-center text-sm text-fg-subtle">
        {{ labels.dataTable.emptyTitle }}
      </li>
      <li
        v-for="row in rows"
        :key="row.id"
        class="atelier-row group"
        :aria-current="String(row.id) === currentId ? 'true' : undefined"
      >
        <input
          v-if="selectable"
          type="checkbox"
          class="atelier-check size-[15px] accent-primary"
          :checked="isSelected(row)"
          :aria-label="labels.dataTable.selectRow(descriptor.rowLabel(row))"
          @change="toggle(row)"
        />
        <span v-else />
        <div class="min-w-0">
          <a
            :href="hrefOf(row)"
            class="flex items-center gap-2 font-semibold no-underline after:absolute after:inset-0"
            :class="row.disabled ? 'text-fg-subtle' : 'text-fg'"
            :aria-current="String(row.id) === currentId ? 'page' : undefined"
          >
            <span
              v-if="badge"
              class="size-[9px] shrink-0 rounded-full"
              :style="row.disabled ? { boxShadow: `inset 0 0 0 1.5px ${colorOf(row)}` } : { background: colorOf(row) }"
              aria-hidden="true"
            />
            <span class="truncate">{{ descriptor.rowLabel(row) }}</span>
          </a>
          <div v-if="metaColumns.length" class="mt-0.5 flex flex-wrap gap-x-3.5 pl-[17px] text-[12.5px] text-fg-subtle">
            <span v-for="column in metaColumns" :key="column.key">
              {{ t(column.label) }}
              <component
                :is="components.get(column.key)"
                v-if="components.has(column.key)"
                :row="row"
                :value="fieldOf(row, column.key)"
              />
              <b v-else class="font-semibold text-fg-muted">{{ screen.cellText(column, row) }}</b>
            </span>
          </div>
        </div>
        <div class="relative z-10 flex items-center gap-1">
          <span
            v-if="row.disabled"
            class="rounded-full bg-warning-soft px-[7px] text-[11.5px] leading-5 font-[650] text-warning"
          >
            {{ t('admin_app.screen.status_disabled') }}
          </span>
          <span class="text-xs text-fg-subtle tabular-nums">#{{ row.id }}</span>
          <UiDropdown
            v-if="rowActions(row).length"
            :label="labels.dataTable.moreActions"
            icon="dots"
            icon-only
            variant="ghost"
            size="sm"
            placement="bottom-end"
          >
            <UiDropdownItem
              v-for="action in rowActions(row)"
              :key="action.id"
              :icon="action.icon"
              :danger="action.id === 'delete'"
              @select="screen.runAction(action.id, row)"
            >
              {{ action.label }}
            </UiDropdownItem>
          </UiDropdown>
        </div>
      </li>
    </ul>

    <div v-if="total > itemsPerPage" class="border-t border-line px-3 py-2">
      <UiPagination v-model:page="page" v-model:items-per-page="itemsPerPage" :total-items="total" />
    </div>
  </section>
</template>

<style scoped>
/* Pastille de filtre : remplie quand elle est active */
.atelier-chip {
  height: 26px;
  padding: 0 10px;
  border: 1px solid var(--line-strong);
  border-radius: 999px;
  background: var(--bg);
  color: var(--fg-muted);
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
}
.atelier-chip[aria-pressed='true'] {
  border-color: var(--fg);
  background: var(--fg);
  color: var(--bg);
}

/* Ligne : la ligne courante prend la couleur du volet et recouvre la bordure de la colonne */
.atelier-row {
  position: relative;
  display: grid;
  grid-template-columns: 18px minmax(0, 1fr) auto;
  align-items: center;
  gap: 10px;
  margin: 1px 0;
  padding: 9px 14px 9px 10px;
  border-radius: 9px 0 0 9px;
}
.atelier-row:hover {
  background: var(--bench-hover);
}
.atelier-row[aria-current='true'] {
  background: var(--bg);
  box-shadow:
    -1px 0 0 var(--line),
    0 -1px 0 var(--line),
    0 1px 0 var(--line);
}
.atelier-row[aria-current='true']::after {
  content: '';
  position: absolute;
  top: 0;
  right: -1px;
  bottom: 0;
  width: 1px;
  background: var(--bg);
}
.atelier-row a:focus-visible {
  outline: none;
}
.atelier-row:has(a:focus-visible) {
  outline: 2px solid var(--focus);
  outline-offset: -2px;
}
.atelier-check {
  position: relative;
  z-index: 1;
  margin: 0;
  opacity: 0;
}
.atelier-row:hover .atelier-check,
.atelier-check:checked,
.atelier-check:focus-visible {
  opacity: 1;
}
@media (hover: none) {
  .atelier-check {
    opacity: 1;
  }
}
</style>
