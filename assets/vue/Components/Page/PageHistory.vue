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
    return { loading: false, history: [] as PageHistoryEntry[] };
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
          <p class="card-subtitle">{{ translate.description }}</p>
        </div>
      </div>

      <div class="p-5">
        <div v-if="history.length === 0" class="flex flex-col items-center justify-center py-10 text-center">
          <p class="text-sm" style="color: var(--text-secondary)">{{ translate.empty }}</p>
        </div>

        <div v-else class="space-y-1">
          <div
            v-for="(entry, index) in history"
            :key="entry.id"
            class="flex items-center justify-between p-3 rounded-xl border border-transparent transition-all"
            style="border-color: transparent"
            @mouseenter="
              ($event.currentTarget as HTMLElement).style.backgroundColor = 'var(--bg-hover)';
              ($event.currentTarget as HTMLElement).style.borderColor = 'var(--border-color)';
            "
            @mouseleave="
              ($event.currentTarget as HTMLElement).style.backgroundColor = '';
              ($event.currentTarget as HTMLElement).style.borderColor = 'transparent';
            "
          >
            <div class="flex items-center gap-4">
              <div
                class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                :style="index === 0 ? 'background-color: var(--primary-lighter)' : 'background-color: var(--bg-hover)'"
              >
                <span
                  class="text-xs font-bold"
                  :style="index === 0 ? 'color: var(--primary)' : 'color: var(--text-secondary)'"
                >
                  #{{ entry.id }}
                </span>
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <p class="text-sm font-semibold" style="color: var(--text-primary)">
                    {{ translate.id }} {{ entry.id }}
                  </p>
                  <span
                    v-if="index === 0"
                    class="text-xs px-2 py-0.5 rounded-full font-semibold"
                    style="background-color: var(--primary-lighter); color: var(--primary)"
                  >
                    {{ translate.action }}
                  </span>
                  <span
                    v-if="entry.id === 0"
                    class="text-xs px-2 py-0.5 rounded-full font-medium"
                    style="background-color: var(--bg-hover); color: var(--text-secondary)"
                  >
                    {{ translate.user }}
                  </span>
                </div>
                <p class="text-xs mt-0.5" style="color: var(--text-secondary)">
                  <span style="color: var(--primary)" v-html="entry.time"></span>
                  &nbsp;·&nbsp;
                  {{ entry.user }}
                </p>
              </div>
            </div>

            <div class="shrink-0 ml-4">
              <button
                type="button"
                class="btn btn-sm btn-outline-dark"
                style="border-color: var(--border-color); color: var(--primary)"
                @mouseenter="
                  ($event.currentTarget as HTMLElement).style.backgroundColor = 'var(--primary-lighter)';
                  ($event.currentTarget as HTMLElement).style.borderColor = 'var(--primary-light)';
                "
                @mouseleave="
                  ($event.currentTarget as HTMLElement).style.backgroundColor = '';
                  ($event.currentTarget as HTMLElement).style.borderColor = 'var(--border-color)';
                "
                @click="restore(entry.id)"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
