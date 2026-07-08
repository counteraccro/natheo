<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { PageHistoryEntry, PageHistoryTranslate, Urls } from '@/ts/Page/type';
import axios from 'axios';
import SkeletonPageHistory from '@/vue/Components/Skeleton/Page/PageHistory.vue';

export default defineComponent({
  name: 'PageHistory',
  components: { SkeletonPageHistory },
  props: {
    id: {
      type: Number as PropType<number>,
      required: true,
    },
    urls: {
      type: Object as PropType<Urls>,
      required: true,
    },
    translate: {
      type: Object as PropType<PageHistoryTranslate>,
      required: true,
    },
    active: {
      type: Boolean as PropType<boolean>,
      required: true,
    },
  },
  emits: ['restore-history'],
  data() {
    return { loading: false, history: [] as PageHistoryEntry[], visibleCount: 10 as number, step: 10 as number };
  },
  computed: {
    visibleHistory(): PageHistoryEntry[] {
      return this.history.slice(0, this.visibleCount);
    },
    hasMore(): boolean {
      return this.visibleCount < this.history.length;
    },
  },
  watch: {
    active(value: boolean) {
      if (value) {
        this.loadHistory();
      }
    },
  },

  methods: {
    /**
     * Charge l'historique
     */
    loadHistory() {
      this.loading = true;
      axios
        .get(this.urls.load_tab_history + '/' + this.id)
        .then((response) => {
          this.history = response.data.history;
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Restaure une page
     * @param id
     */
    restore(id: number) {
      this.loading = true;

      axios
        .post(this.urls.reload_page_history, {
          row_id: id,
          id: this.id,
        })
        .then((response) => {
          this.$emit('restore-history', response.data.page, response.data.msg);
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Pagination
     */
    loadMore() {
      this.visibleCount += this.step;
    },
  },
});
</script>

<template>
  <SkeletonPageHistory v-if="loading" />
  <div v-else>
    <div class="card mb-4">
      <div class="card-header">
        <div>
          <div class="card-title">
            <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke="currentColor"
                stroke-linecap="round"
                stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            {{ translate.title }}
          </div>
          <div class="card-subtitle">{{ translate.description }}</div>
        </div>
      </div>

      <div class="p-5">
        <div class="divide-y" style="border-color: var(--border-color)">
          <div
            v-for="(entry, index) in visibleHistory"
            :key="entry.id"
            class="flex items-center gap-3 py-2 px-2 rounded-lg"
            style="border: 1px solid transparent"
            @mouseenter="
              ($event.currentTarget as HTMLElement).style.backgroundColor = 'var(--bg-hover)';
              ($event.currentTarget as HTMLElement).style.borderColor = 'var(--border-color)';
            "
            @mouseleave="
              ($event.currentTarget as HTMLElement).style.backgroundColor = '';
              ($event.currentTarget as HTMLElement).style.borderColor = 'transparent';
            "
          >
            <span
              class="text-xs font-semibold shrink-0 px-1.5 py-0.5 rounded"
              :style="
                index === 0
                  ? 'background-color: var(--primary-lighter); color: var(--primary); min-width: 40px; text-align: center;'
                  : 'background-color: var(--bg-hover); color: var(--text-secondary); border: 1px solid var(--border-color); min-width: 40px; text-align: center;'
              "
            >
              #{{ entry.id }}
            </span>

            <span
              class="text-xs flex-1"
              :style="index === 0 ? 'color: var(--primary)' : 'color: var(--text-secondary)'"
              v-html="entry.time"
            ></span>

            <span class="text-xs shrink-0" style="color: var(--text-light)">{{ entry.user }}</span>

            <span
              v-if="index === 0"
              class="text-xs font-semibold px-2 py-0.5 rounded-full shrink-0"
              style="background-color: var(--primary-lighter); color: var(--primary)"
            >
              {{ translate.action }}
            </span>

            <span
              v-if="entry.id === 0"
              class="text-xs font-semibold px-2 py-0.5 rounded-full shrink-0"
              style="
                background-color: var(--bg-hover);
                color: var(--text-secondary);
                border: 1px solid var(--border-color);
              "
            >
              {{ translate.user }}
            </span>

            <button
              type="button"
              class="btn btn-xs shrink-0"
              :class="index === 0 ? 'btn-outline-primary' : 'btn-outline-dark'"
              @click="restore(entry.id)"
            >
              <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                />
              </svg>
              {{ translate.reload }}
            </button>
          </div>
        </div>

        <div v-if="hasMore" class="flex justify-center pt-4">
          <button type="button" class="btn btn-sm btn-outline-dark" @click="loadMore">
            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
            {{ translate.load_more }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
