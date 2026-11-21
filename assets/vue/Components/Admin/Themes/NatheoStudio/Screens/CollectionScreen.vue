<script setup lang="ts" generic="T extends ScreenItem">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Rendu Natheo-Studio d'un écran de liste : en-tête, filtres, grille, actions par ligne et groupées, requête SQL
 */
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { AdminPageHeader, UiBadge, UiButton, UiDataTable, UiSelect, type DataTableRow } from '@ui';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { toComponent } from '@/vue/Composables/Admin/Screen/adminTheme';
import { formatDateTime } from '@/vue/Composables/Admin/Screen/screenFormat';
import type { AdminScreenProps } from '@/ts/Admin/Theme/AdminTheme.type';
import type { CollectionColumn, CollectionScreenState } from '@/ts/Admin/Screen/CollectionScreen.type';
import type { ScreenAuthor, ScreenItem } from '@/ts/Admin/Screen/Screen.type';

const props = defineProps<AdminScreenProps<CollectionScreenState<T>>>();

const { t, bootstrap } = useAdminContext();
const router = useRouter();
const descriptor = props.screen.descriptor;
const { items, total, loading, sort, page, itemsPerPage, search, selected, sql } = props.screen.collection;
const { savedSql } = props.screen;
const { status, author } = props.screen.filter;

const createHref = computed(() => (descriptor.create ? router.resolve({ name: descriptor.create.route }).href : ''));
const customColumns = descriptor.columns.filter((column) => (column.type ?? 'text') !== 'text' || column.component);
const components = new Map(
  customColumns
    .filter((column) => column.component)
    .map((column) => [column.key, toComponent(column.component!)] as const)
);

const statusOptions = [
  { value: '', label: t('admin_app.screen.filter_status_all') },
  { value: 'enabled', label: t('admin_app.screen.filter_status_enabled') },
  { value: 'disabled', label: t('admin_app.screen.filter_status_disabled') },
];
const authorOptions = [
  { value: '', label: t('admin_app.screen.filter_author_all') },
  { value: 'me', label: t('admin_app.screen.filter_author_me') },
];

const rows = computed(() => items.value as unknown as DataTableRow[]);
const asItem = (row: DataTableRow): T => row as unknown as T;
const colorOf = (column: CollectionColumn, row: DataTableRow): string =>
  column.color ? String(row[column.color] ?? '') : '';
const authorOf = (value: unknown): string => (value as ScreenAuthor | null)?.login ?? '—';

onMounted(() => void props.screen.collection.load());
</script>

<template>
  <AdminPageHeader
    :title="t(descriptor.title)"
    :description="descriptor.description ? t(descriptor.description) : undefined"
    :icon="descriptor.icon"
  >
    <template v-if="screen.canCreate && descriptor.create" #actions>
      <UiButton variant="primary" icon="plus" :href="createHref">{{ t(descriptor.create.label) }}</UiButton>
    </template>
  </AdminPageHeader>

  <UiDataTable
    v-model:sort="sort"
    v-model:page="page"
    v-model:items-per-page="itemsPerPage"
    v-model:selected="selected"
    :label="t(descriptor.title)"
    :columns="screen.columns"
    :rows="rows"
    :total-items="total"
    :loading="loading"
    :actions-for="(row) => screen.actionsFor(asItem(row))"
    :row-label="(row) => descriptor.rowLabel(asItem(row))"
    :row-class="
      (row) => (row.disabled ? 'bg-[color-mix(in_srgb,var(--surface)_60%,transparent)] text-fg-subtle' : undefined)
    "
    :selectable="screen.bulkActions.length > 0"
    :bulk-actions="screen.bulkActions"
    :sql="screen.canSql ? sql : null"
    :sql-saved="sql !== null && savedSql === sql"
    @search="search = $event"
    @action="({ id, row }) => screen.runAction(id, asItem(row))"
    @bulk-action="({ id, keys }) => screen.runBulk(id, keys)"
    @select-all="screen.selectAll()"
    @save-sql="screen.saveSql($event)"
  >
    <template v-if="descriptor.filters?.length" #filters>
      <div v-if="descriptor.filters.includes('status')" class="w-44">
        <UiSelect
          v-model="status"
          size="sm"
          :options="statusOptions"
          :aria-label="t('admin_app.screen.filter_status')"
        />
      </div>
      <div v-if="descriptor.filters.includes('author')" class="w-44">
        <UiSelect
          v-model="author"
          size="sm"
          :options="authorOptions"
          :aria-label="t('admin_app.screen.filter_author')"
        />
      </div>
    </template>

    <template v-for="column in customColumns" :key="column.key" #[`cell-${column.key}`]="{ row, value }">
      <component :is="components.get(column.key)" v-if="components.has(column.key)" :row="row" :value="value" />
      <span v-else-if="column.type === 'badge'" class="inline-flex items-center gap-2">
        <span
          class="inline-flex h-[26px] items-center gap-[7px] rounded-full bg-surface pr-2.5 pl-2 text-sm font-semibold whitespace-nowrap shadow-[inset_0_0_0_1px_var(--line)]"
          :class="row.disabled ? 'text-fg-subtle' : 'text-fg'"
        >
          <span
            class="size-2 rounded-full"
            :style="
              row.disabled
                ? { boxShadow: `inset 0 0 0 1.5px ${colorOf(column, row)}` }
                : { background: colorOf(column, row) }
            "
            aria-hidden="true"
          />
          {{ value }}
        </span>
        <UiBadge v-if="row.disabled" tone="warning" :dot="false">{{ t('admin_app.screen.status_disabled') }}</UiBadge>
      </span>
      <time v-else-if="column.type === 'date'" :datetime="String(value ?? '')" class="whitespace-nowrap">
        {{ formatDateTime(value as string | null, bootstrap.locale) }}
      </time>
      <template v-else-if="column.type === 'author'">{{ authorOf(value) }}</template>
      <template v-else>{{ value }}</template>
    </template>
  </UiDataTable>
</template>
