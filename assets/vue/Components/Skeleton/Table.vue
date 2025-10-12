<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Skeleton d'un tableau
 */
export default {
  name: 'SkeletonTable',
  props: {
    rows: {
      type: Number,
      default: 5,
    },
    columns: {
      type: Number,
      default: 3,
    },
  },
  emits: [],
  data() {
    return {
      value: 'Value',
    };
  },
  mounted() {},

  methods: {
    getRandomWidth() {
      const widths = ['50%', '66%', '75%', '80%'];
      return widths[Math.floor(Math.random() * widths.length)];
    },
  },
};
</script>

<template>
  <div class="w-full max-w-4xl mx-auto p-6">
    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow">
      <!-- Header -->
      <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-6 py-4">
        <div class="grid gap-4" :style="{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }">
          <div v-for="col in columns" :key="col" class="h-4 bg-gray-300 dark:bg-gray-600 rounded animate-pulse"></div>
        </div>
      </div>

      <!-- Rows -->
      <div class="divide-y divide-gray-200 dark:divide-gray-700">
        <div v-for="row in rows" :key="row" class="px-6 py-4">
          <div class="grid gap-4" :style="{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }">
            <div v-for="col in columns" :key="col" class="space-y-2">
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
              <div
                class="h-3 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"
                :style="{ width: getRandomWidth() }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
