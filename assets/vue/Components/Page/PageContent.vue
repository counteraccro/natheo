<script lang="ts">
/**
 * Gestionnaire des Pages - Onglet Content - Gestion des contents
 * @author Gourdon Aymeric
 * @version 2.0
 */

import { defineComponent, PropType } from 'vue';
import { Locales, Page, PageTranslations, Urls, PageData } from '@/ts/Page/type';
import PageContentBlock from '@/vue/Components/Page/PageContentBlock.vue';

interface LayoutCol {
  renderBlockId: number;
}

interface LayoutRow {
  row: number;
  cols: LayoutCol[];
}

interface BlockNeighbors {
  left: number | null;
  right: number | null;
  up: number | null;
  down: number | null;
}

const LAYOUTS: Record<number, LayoutRow[]> = {
  1: [{ row: 1, cols: [{ renderBlockId: 1 }] }],
  2: [{ row: 1, cols: [{ renderBlockId: 1 }, { renderBlockId: 2 }] }],
  3: [{ row: 1, cols: [{ renderBlockId: 1 }, { renderBlockId: 2 }, { renderBlockId: 3 }] }],
  4: [
    { row: 1, cols: [{ renderBlockId: 1 }] },
    { row: 2, cols: [{ renderBlockId: 2 }] },
  ],
  5: [
    { row: 1, cols: [{ renderBlockId: 1 }] },
    { row: 2, cols: [{ renderBlockId: 2 }] },
    { row: 3, cols: [{ renderBlockId: 3 }] },
  ],
  6: [
    { row: 1, cols: [{ renderBlockId: 1 }] },
    { row: 2, cols: [{ renderBlockId: 2 }, { renderBlockId: 3 }] },
  ],
  7: [
    { row: 1, cols: [{ renderBlockId: 1 }, { renderBlockId: 2 }] },
    { row: 2, cols: [{ renderBlockId: 3 }] },
  ],
  8: [
    { row: 1, cols: [{ renderBlockId: 1 }, { renderBlockId: 2 }] },
    { row: 2, cols: [{ renderBlockId: 3 }, { renderBlockId: 4 }] },
  ],
};

export default defineComponent({
  name: 'PageContent',
  components: { PageContentBlock },
  props: {
    translate: {
      type: Object as PropType<PageTranslations>,
      required: true,
    },
    page: {
      type: Object as PropType<Page>,
      required: true,
    },
    currentLocale: {
      type: String as PropType<string>,
      required: true,
    },
    locales: {
      type: Object as PropType<Locales>,
      required: true,
    },
    pageDatas: {
      type: Object as PropType<PageData>,
      required: true,
    },
    restoreCount: {
      type: Number as PropType<number>,
      required: true,
    },
    urls: {
      type: Object as PropType<Urls>,
      required: true,
    },
  },
  emits: ['update-page-contents', 'swap-blocks'],
  computed: {
    layout(): LayoutRow[] {
      return LAYOUTS[this.page.render] ?? LAYOUTS[1];
    },
    neighbors(): Record<number, BlockNeighbors> {
      const result: Record<number, BlockNeighbors> = {};
      for (let rowIdx = 0; rowIdx < this.layout.length; rowIdx++) {
        const row = this.layout[rowIdx];
        for (let colIdx = 0; colIdx < row.cols.length; colIdx++) {
          const blockId = row.cols[colIdx].renderBlockId;
          const prevRow = rowIdx > 0 ? this.layout[rowIdx - 1] : null;
          const nextRow = rowIdx < this.layout.length - 1 ? this.layout[rowIdx + 1] : null;
          result[blockId] = {
            left: colIdx > 0 ? row.cols[colIdx - 1].renderBlockId : null,
            right: colIdx < row.cols.length - 1 ? row.cols[colIdx + 1].renderBlockId : null,
            up: prevRow ? prevRow.cols[Math.min(colIdx, prevRow.cols.length - 1)].renderBlockId : null,
            down: nextRow ? nextRow.cols[Math.min(colIdx, nextRow.cols.length - 1)].renderBlockId : null,
          };
        }
      }
      return result;
    },
  },
});
</script>

<template>
  <div class="card mb-4">
    <div class="card-header">
      <div class="card-title">
        <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-width="2"
            d="M5.005 11.19V12l6.998 4.042L19 12v-.81M5 16.15v.81L11.997 21l6.998-4.042v-.81M12.003 3 5.005 7.042l6.998 4.042L19 7.042 12.003 3Z"
          />
        </svg>
        {{ translate.page_content.title }}
      </div>
    </div>
    <div class="p-4 flex flex-col gap-4">
      <div class="form-group">
        <label for="list-render-page" class="form-label">{{ translate.page_content_form.list_render_label }}</label>
        <select id="list-render-page" class="form-input" v-model="page.render">
          <option v-for="(value, key) in pageDatas.list_render" :value="parseInt(key)">{{ value }}</option>
        </select>
        <div id="list-status-help" class="form-text">{{ translate.page_content_form.list_render_help }}</div>
      </div>

      <div
        v-for="row in layout"
        :key="row.row"
        class="grid gap-4"
        :style="'grid-template-columns: repeat(' + row.cols.length + ', 1fr)'"
      >
        <PageContentBlock
          v-for="col in row.cols"
          :key="col.renderBlockId"
          :render-block-id="col.renderBlockId"
          :neighbors="neighbors[col.renderBlockId]"
          :page="page"
          :translate="translate"
          :current-locale="currentLocale"
          :urls="urls"
          :locales="locales"
          :page-datas="pageDatas"
          :restore-count="restoreCount"
          @update-page-contents="$emit('update-page-contents', $event)"
          @swap-blocks="(blockA, blockB) => $emit('swap-blocks', blockA, blockB)"
        />
      </div>
    </div>
  </div>
</template>
