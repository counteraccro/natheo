<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'SkeletonFaq',
  props: {
    isNew: {
      type: Boolean,
      default: false,
    },
  },
});
</script>

<template>
  <div class="rounded-xl p-5 mb-7 animate-pulse" style="background: linear-gradient(135deg, #c4b5fd 0%, #ddd6fe 100%)">
    <div class="h-2.5 w-24 rounded bg-white/40 mb-3"></div>
    <div class="h-11 w-full rounded-lg bg-white/30"></div>
    <div class="h-2.5 w-64 rounded bg-white/30 mt-3"></div>
  </div>

  <div
    v-if="!isNew"
    class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-card)] mb-4 overflow-hidden"
    style="box-shadow: var(--shadow-sm)"
  >
    <!-- Header -->
    <div class="flex items-center gap-3 px-4 py-3 border-b border-[var(--border-color)] bg-gray-50 animate-pulse">
      <div class="w-4 h-4 rounded bg-gray-200 flex-shrink-0"></div>
      <div class="w-2 h-2 rounded-full bg-gray-300 flex-shrink-0"></div>
      <div class="flex-1 h-4 w-20 rounded bg-gray-200"></div>
      <div class="h-5 w-20 rounded-full bg-gray-200"></div>
      <div class="w-5 h-5 rounded bg-gray-200"></div>
    </div>
    <!-- Questions -->
    <div class="p-3 space-y-2">
      <!-- Q1 skeleton -->
      <div class="border border-[var(--border-color)] rounded-lg p-3 animate-pulse">
        <div class="flex items-center gap-2.5">
          <div class="w-3.5 h-3.5 rounded bg-gray-200 flex-shrink-0"></div>
          <div class="w-5 h-3 rounded bg-gray-200 flex-shrink-0"></div>
          <div class="flex-1 h-3.5 rounded bg-gray-200"></div>
          <div class="w-3.5 h-3.5 rounded bg-gray-200"></div>
        </div>
      </div>
      <!-- Q2 skeleton – ouvert -->
      <div class="border border-gray-200 rounded-lg overflow-hidden animate-pulse">
        <div class="flex items-center gap-2.5 p-3">
          <div class="w-3.5 h-3.5 rounded bg-gray-200 flex-shrink-0"></div>
          <div class="w-5 h-3 rounded bg-gray-200 flex-shrink-0"></div>
          <div class="flex-1 h-3.5 rounded bg-gray-200"></div>
          <div class="w-3.5 h-3.5 rounded bg-gray-200"></div>
        </div>
        <!-- Éditeur skeleton -->
        <div class="border-t border-gray-100 bg-gray-50 px-4 py-4 pl-10 space-y-4">
          <!-- Champ question -->
          <div>
            <div class="h-2.5 w-16 rounded bg-gray-200 mb-2"></div>
            <div class="h-9 w-full rounded-lg bg-gray-200"></div>
          </div>
          <!-- Champ réponse -->
          <div>
            <div class="h-2.5 w-14 rounded bg-gray-200 mb-2"></div>
            <div class="h-20 w-full rounded-lg bg-gray-200"></div>
          </div>
          <!-- Footer -->
          <div class="flex items-center justify-between">
            <div class="h-7 w-44 rounded-md bg-gray-200"></div>
            <div class="flex gap-1.5">
              <div class="w-7 h-7 rounded-md bg-gray-200"></div>
              <div class="w-7 h-7 rounded-md bg-gray-200"></div>
            </div>
          </div>
        </div>
      </div>
      <!-- Add question btn skeleton -->
      <div class="h-9 w-full rounded-lg border-2 border-dashed border-gray-200 animate-pulse"></div>
    </div>
  </div>
</template>

<style scoped></style>
