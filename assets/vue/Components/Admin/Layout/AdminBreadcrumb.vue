<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fil d'Ariane : dernier élément marqué comme page courante
 */
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import type { BreadcrumbItem } from '@/ts/Admin/Layout/AdminBreadcrumb.type';

defineProps<{ items: BreadcrumbItem[] }>();
const labels = useUiLabels();
</script>

<template>
  <nav :aria-label="labels.breadcrumb.nav">
    <ol class="m-0 flex list-none flex-wrap items-center gap-1 p-0 text-sm text-fg-subtle">
      <li v-for="(item, index) in items" :key="index" class="flex items-center gap-1">
        <UiIcon v-if="index > 0" name="chevron-right" class="size-3.5 opacity-60" />
        <span v-if="index === items.length - 1" aria-current="page" class="px-1 py-0.5 font-medium text-fg">{{
          item.label
        }}</span>
        <a
          v-else-if="item.href"
          :href="item.href"
          class="rounded-[4px] px-1 py-0.5 text-inherit no-underline hover:bg-surface hover:text-fg"
          >{{ item.label }}</a
        >
        <span v-else class="px-1 py-0.5">{{ item.label }}</span>
      </li>
    </ol>
  </nav>
</template>
