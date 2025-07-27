<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Structure de la page de front en loading
 */
export default {
  name: 'Skeleton',
  props: {},
  emits: [],
  data() {
    return {}
  },
  mounted() {

  },

  methods: {}
}
</script>

<template>

  <!-- header -->
  <div class="mx-auto w-full rounded-md border border-gray-300 p-4">
    <div class="flex animate-pulse space-x-4">
      <div class="size-10 rounded-full bg-gray-200"></div>
      <div class="flex-1 space-y-6 py-1">
        <div class="h-2 rounded bg-gray-200"></div>
        <div class="space-y-3">
          <div class="grid grid-cols-12 gap-4">
            <div class="col-span-2 h-2 rounded bg-gray-200"></div>
            <div class="col-span-1 h-2 rounded bg-gray-200"></div>
            <div class="col-span-1 h-2 rounded bg-gray-200"></div>
            <div class="col-span-1 h-2 rounded bg-gray-200"></div>
            <div class="col-span-1 h-2 rounded bg-gray-200"></div>
            <div class="col-span-1 h-2 rounded bg-gray-200"></div>
            <div class="col-span-1 h-2 rounded bg-gray-200"></div>
            <div class="col-span-1 h-2 rounded bg-gray-200"></div>
            <div class="col-span-1 h-2 rounded bg-gray-200"></div>
            <div class="col-span-1 h-2 rounded bg-gray-200"></div>
            <div class="col-span-1 h-2 rounded bg-gray-200"></div>
          </div>
          <div class="h-2 rounded bg-gray-200"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- body -->
  <div class="grid grid-cols-12 gap-2 mt-2 border border-gray-300 p-4 animate-pulse">
    <div class="col-span-12 md:col-span-2">
      <div class="h-4 bg-gray-300 rounded w-50"></div>
      <div class="h-4 bg-gray-200 rounded w-25 mt-2"></div>
      <div class="h-4 bg-gray-200 rounded w-25 mt-2"></div>
      <div class="h-4 bg-gray-200 rounded w-25 mt-2"></div>
      <div class="h-4 bg-gray-300 rounded w-50 mt-2"></div>
      <div class="h-4 bg-gray-200 rounded w-25 mt-2"></div>
      <div class="h-4 bg-gray-200 rounded w-25 mt-2"></div>
      <div class="h-4 bg-gray-200 rounded w-25 mt-2"></div>
      <div class="h-4 bg-gray-300 rounded w-50 mt-2"></div>
      <div class="h-4 bg-gray-200 rounded w-25 mt-2"></div>
      <div class="h-4 bg-gray-200 rounded w-25 mt-2"></div>
      <div class="h-4 bg-gray-200 rounded w-25 mt-2"></div>
    </div>
    <div class="col-span-12 md:col-span-10">

      <div class="mx-auto w-full rounded-md p-4">
        <div class="flex animate-pulse space-x-4">
          <div class="size-10 rounded-full bg-gray-200"></div>
          <div class="flex-1 space-y-6 py-1">
            <div class="h-2 rounded bg-gray-200"></div>
            <div class="space-y-3">
              <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2 h-2 rounded bg-gray-200"></div>
                <div class="col-span-1 h-2 rounded bg-gray-200"></div>
              </div>
              <div class="h-2 rounded bg-gray-200"></div>
              <div class="h-2 bg-gray-200 rounded mt-2"></div>
              <div class="mt-6"></div>
              <div class="h-4 bg-gray-200 rounded w-100 mt-2"></div>
              <div class="h-2 bg-gray-200 rounded mt-2"></div>
              <div class="h-2 bg-gray-200 rounded mt-2"></div>
              <div class="h-2 bg-gray-200 rounded mt-2 w-100"></div>

              <div class="mt-6"></div>
              <div class="h-4 bg-gray-200 rounded w-100 mt-2"></div>
              <div class="h-2 bg-gray-200 rounded mt-2"></div>
              <div class="h-2 bg-gray-200 rounded mt-2"></div>
              <div class="h-2 bg-gray-200 rounded mt-2 w-100"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <div class="w-full animate-pulse border border-gray-300 mt-2 p-2">
    <div class="text-center">
      <p class="w-32 h-2 mx-auto bg-gray-200 rounded-lg"></p>

      <div class="flex flex-wrap justify-center gap-4 mt-10">
        <p class="w-24 h-2 bg-gray-200 rounded-lg"></p>
        <p class="w-24 h-2 bg-gray-200 rounded-lg"></p>
        <p class="w-24 h-2 bg-gray-200 rounded-lg"></p>
        <p class="w-24 h-2 bg-gray-200 rounded-lg"></p>
        <p class="w-24 h-2 bg-gray-200 rounded-lg"></p>
      </div>

    </div>

    <hr class="my-6 border-gray-200 md:my-10" />

    <div class="flex flex-col items-center sm:flex-row sm:justify-between">
      <p class="w-24 h-2 bg-gray-200 rounded-lg"></p>

      <p class="w-64 h-2 bg-gray-200 rounded-lg"></p>
    </div>
  </div>
</template>