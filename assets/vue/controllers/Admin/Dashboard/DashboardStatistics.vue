<script lang="ts">
import { defineComponent, PropType } from 'vue';
import SkeletonDashboardStatistiques from '@/vue/Components/Skeleton/Dashboard/DashboardStatistiques.vue';
import {
  DashboardStatisticsResponse,
  DashboardStatisticsTranslate,
  Urls,
} from '@/ts/Dashboard/dashboardStatistics.type';
import axios from 'axios';

export default defineComponent({
  name: 'DashboardStatistiques',
  components: { SkeletonDashboardStatistiques },
  props: {
    translate: {
      type: Object as PropType<DashboardStatisticsTranslate>,
      required: true,
    },
    urls: {
      type: Object as PropType<Urls>,
      required: true,
    },
  },
  data() {
    return {
      loading: false,
      stats: {
        nbPage: 0,
        nbComments: 0,
        nbWaitComments: 0,
        nbUsers: 0,
        nbViews: 0,
      } as DashboardStatisticsResponse,
    };
  },
  mounted() {
    this.loadStatistic();
  },
  methods: {
    loadStatistic(): void {
      this.loading = true;
      axios
        .get<DashboardStatisticsResponse>(this.urls.stats, {})
        .then((response) => {
          this.stats = response.data;
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
  <SkeletonDashboardStatistiques v-if="loading" />
  <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
    <div class="card rounded-lg p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium" style="color: var(--text-secondary)">{{ translate.total_article }}</p>
          <p class="text-3xl font-bold mt-2">{{ stats.nbPage }}</p>
          <p class="text-xs mt-1" style="color: var(--text-light)">
            <a :href="urls.page" class="flex gap-0.5 items-center"
              >{{ translate.link_pages }}
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-width="2" d="M17 8l4 4m0 0-4 4m4-4H3"></path></svg
            ></a>
          </p>
        </div>
        <div class="p-3 rounded-lg" style="background-color: color-mix(in srgb, var(--primary) 10%, transparent)">
          <svg class="w-8 h-8" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 3v4a1 1 0 0 1-1 1H5m4 8h6m-6-4h6m4-8v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1Z"
            ></path>
          </svg>
        </div>
      </div>
    </div>

    <div class="card rounded-lg p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium" style="color: var(--text-secondary)">{{ translate.comments }}</p>
          <p class="text-3xl font-bold mt-2">{{ stats.nbComments }}</p>
          <p class="text-xs mt-1" style="color: var(--text-light)">
            <a :href="urls.comment" class="flex gap-0.5 items-center"
              >{{ stats.nbWaitComments }} {{ translate.comments_waiting }}
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-width="2" d="M17 8l4 4m0 0-4 4m4-4H3"></path></svg
            ></a>
          </p>
        </div>
        <div
          class="p-3 rounded-lg"
          style="background-color: color-mix(in srgb, var(--status-validated) 10%, transparent)"
        >
          <svg
            class="w-8 h-8"
            style="color: var(--status-validated)"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 17h6l3 3v-3h2V9h-2M4 4h11v8H9l-3 3v-3H4V4Z"
            ></path>
          </svg>
        </div>
      </div>
    </div>

    <div class="card rounded-lg p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium" style="color: var(--text-secondary)">{{ translate.users }}</p>
          <p class="text-3xl font-bold mt-2">{{ stats.nbUsers }}</p>
          <p class="text-xs mt-1" style="color: var(--text-light)">
            <a :href="urls.user" class="flex gap-0.5 items-center"
              >{{ translate.link_users }}
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-width="2" d="M17 8l4 4m0 0-4 4m4-4H3"></path></svg
            ></a>
          </p>
        </div>
        <div class="p-3 rounded-lg" style="background-color: color-mix(in srgb, var(--secondary) 10%, transparent)">
          <svg class="w-8 h-8" style="color: var(--secondary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4.5 17H4a1 1 0 0 1-1-1 3 3 0 0 1 3-3h1m0-3.05A2.5 2.5 0 1 1 9 5.5M19.5 17h.5a1 1 0 0 0 1-1 3 3 0 0 0-3-3h-1m0-3.05a2.5 2.5 0 1 0-2-4.45m.5 13.5h-7a1 1 0 0 1-1-1 3 3 0 0 1 3-3h3a3 3 0 0 1 3 3 1 1 0 0 1-1 1Zm-1-9.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"
            ></path>
          </svg>
        </div>
      </div>
    </div>

    <div class="card rounded-lg p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium" style="color: var(--text-secondary)">{{ translate.views }}</p>
          <p class="text-3xl font-bold mt-2">{{ stats.nbViews }}</p>
          <p class="text-xs mt-1" style="color: var(--text-light)"></p>
        </div>
        <div
          class="p-3 rounded-lg"
          style="background-color: color-mix(in srgb, var(--status-pending) 10%, transparent)"
        >
          <svg
            class="w-8 h-8"
            style="color: var(--status-pending)"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 4v15a1 1 0 0 0 1 1h15M8 16l2.5-5.5 3 3L17.273 7 20 9.667"
            ></path>
          </svg>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
