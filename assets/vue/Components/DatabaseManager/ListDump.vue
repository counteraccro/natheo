<script>
/**
 * Retourne la liste des dumps
 * @author Gourdon Aymeric
 * @version 2.0
 */

export default {
  name: 'ListDump',
  components: {},
  props: {
    data: Object,
    translate: Object,
  },
  emits: ['refresh-dump'],
  data() {
    return {};
  },
  mounted() {},
  computed: {},
  methods: {},
};
</script>

<template>
  <div class="p-4 sm:p-6 border-b" style="border-color: var(--border-color)">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <p class="text-sm" style="color: var(--text-secondary)">{{ translate.title }}</p>
      <button type="button" class="btn btn-outline-dark btn-md" @click="$emit('refresh-dump')">
        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
          ></path>
        </svg>
        {{ translate.btn_refresh }}
      </button>
    </div>
  </div>

  <div class="divide-y divide-[var(--border-color)]">
    <div class="p-4 sm:p-6 hover:bg-opacity-50 transition-colors" style="background-color: var(--bg-card)">
      <div
        v-if="this.data.length"
        v-for="row in this.data"
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
      >
        <div class="flex items-start gap-4">
          <div class="p-3 rounded-lg" style="background-color: var(--primary-lighter)">
            <svg class="w-6 h-6" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"
              ></path>
            </svg>
          </div>
          <div>
            <h4 class="font-semibold" style="color: var(--text-primary)">{{ row.name }}</h4>
            <div class="flex flex-wrap items-center gap-3 mt-1 text-xs" style="color: var(--text-secondary)">
              <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                  ></path>
                </svg>
                {{ row.date }}
              </span>
              <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                  ></path>
                </svg>
                {{ row.size }}
              </span>
              <span
                class="px-2 py-0.5 rounded-full text-xs font-medium"
                style="background-color: var(--primary-lighter); color: var(--primary)"
                >{{ row.extension }}</span
              >
            </div>
          </div>
        </div>
        <div class="flex gap-2">
          <a :href="row.url" class="btn btn-xs btn-primary" style="background-color: var(--primary)">
            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
              ></path>
            </svg>
            {{ translate.btn_download }}
          </a>
          <button class="btn btn-icon btn-xs btn-ghost-danger">
            <svg class="icon-sm" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <path
                stroke="currentColor"
                stroke-width="2"
                d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"
              ></path>
            </svg>
          </button>
        </div>
      </div>
      <div v-else>
        <div class="text-center py-6 text-[var(--text-secondary)]">
          <svg
            class="w-16 h-16 mx-auto mb-4 text-[var(-text-light)]"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.5"
              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            ></path>
          </svg>
          <p class="text-sm font-medium">{{ translate.no_data }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
