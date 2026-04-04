<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Skeleton d'un form
 */
export default {
  name: 'SkeletonMediatheque',
  props: {},
  emits: [],
  data() {
    return {};
  },
  mounted() {},

  methods: {},
};
</script>

<template>
  <div class="bg-white border border-gray-200 rounded-xl overflow-hidden animate-pulse">
    <!-- Path bar -->
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gray-50">
      <div class="flex items-center gap-2">
        <div class="h-7 bg-gray-200 rounded-md w-24"></div>
        <div class="w-3 h-3 bg-gray-200 rounded"></div>
        <div class="h-5 bg-gray-200 rounded w-16"></div>
      </div>
      <div class="h-6 bg-gray-200 rounded-full w-28"></div>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 border-b border-gray-100">
      <div class="flex items-center gap-2">
        <div class="h-9 bg-gray-200 rounded-lg w-36"></div>
        <div class="h-9 bg-gray-200 rounded-lg w-32"></div>
      </div>
      <div class="flex items-center gap-2">
        <div class="h-9 bg-gray-200 rounded-lg w-28"></div>
        <div class="h-9 w-9 bg-gray-200 rounded-lg"></div>
        <div class="h-9 w-9 bg-gray-200 rounded-lg"></div>
        <div class="flex rounded-lg overflow-hidden border border-gray-200">
          <div class="h-9 w-9 bg-gray-200"></div>
          <div class="h-9 w-9 bg-gray-100 border-l border-gray-200"></div>
        </div>
      </div>
    </div>

    <!-- Grid -->
    <div class="p-4">
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
        <!-- Dossier 1 (avec aperçus) -->
        <div class="rounded-xl overflow-hidden border border-gray-200">
          <div class="relative w-full bg-gray-100" style="padding-top: 66.66%">
            <div class="absolute inset-0 flex flex-col justify-end gap-1.5 p-3">
              <div class="flex-1 flex items-center justify-center">
                <div class="w-10 h-10 bg-gray-200 rounded-lg"></div>
              </div>
              <div class="grid grid-cols-3 gap-1 rounded-md overflow-hidden" style="height: 28px">
                <div class="bg-gray-300"></div>
                <div class="bg-gray-300"></div>
                <div class="bg-gray-200"></div>
              </div>
            </div>
            <div class="absolute top-2 right-2 h-4 w-12 bg-gray-300 rounded"></div>
          </div>
          <div class="flex items-center justify-between px-3 py-2.5 border-t border-gray-200">
            <div class="flex-1 min-w-0 space-y-1.5">
              <div class="h-3 bg-gray-200 rounded w-3/4"></div>
              <div class="h-2.5 bg-gray-100 rounded w-1/2"></div>
            </div>
            <div class="w-4 h-4 bg-gray-200 rounded ml-2 flex-shrink-0"></div>
          </div>
        </div>

        <!-- Dossier 2 (vide) -->
        <div class="rounded-xl overflow-hidden border border-gray-200">
          <div class="relative w-full bg-gray-100" style="padding-top: 66.66%">
            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2">
              <div class="w-12 h-12 bg-gray-200 rounded-xl"></div>
              <div class="h-2.5 bg-gray-200 rounded w-8"></div>
            </div>
            <div class="absolute top-2 right-2 h-4 w-12 bg-gray-300 rounded"></div>
          </div>
          <div class="flex items-center justify-between px-3 py-2.5 border-t border-gray-200">
            <div class="flex-1 min-w-0 space-y-1.5">
              <div class="h-3 bg-gray-200 rounded w-4/5"></div>
              <div class="h-2.5 bg-gray-100 rounded w-2/5"></div>
            </div>
            <div class="w-4 h-4 bg-gray-200 rounded ml-2 flex-shrink-0"></div>
          </div>
        </div>

        <!-- Images ×8 — largeurs variées pour simuler de vrais noms -->

        <div class="rounded-xl overflow-hidden border border-gray-200">
          <div class="relative w-full bg-gray-200" style="padding-top: 66.66%">
            <div class="absolute top-2 right-2 h-4 w-8 bg-gray-300 rounded"></div>
          </div>
          <div class="flex items-center justify-between px-3 py-2.5 border-t border-gray-200">
            <div class="flex-1 min-w-0 space-y-1.5">
              <div class="h-3 bg-gray-200 rounded w-3/4"></div>
              <div class="h-2.5 bg-gray-100 rounded w-1/2"></div>
            </div>
            <div class="w-4 h-4 bg-gray-200 rounded ml-2 flex-shrink-0"></div>
          </div>
        </div>

        <div class="rounded-xl overflow-hidden border border-gray-200">
          <div class="relative w-full bg-gray-200" style="padding-top: 66.66%">
            <div class="absolute top-2 right-2 h-4 w-8 bg-gray-300 rounded"></div>
          </div>
          <div class="flex items-center justify-between px-3 py-2.5 border-t border-gray-200">
            <div class="flex-1 min-w-0 space-y-1.5">
              <div class="h-3 bg-gray-200 rounded w-2/3"></div>
              <div class="h-2.5 bg-gray-100 rounded w-2/5"></div>
            </div>
            <div class="w-4 h-4 bg-gray-200 rounded ml-2 flex-shrink-0"></div>
          </div>
        </div>

        <div class="rounded-xl overflow-hidden border border-gray-200">
          <div class="relative w-full bg-gray-200" style="padding-top: 66.66%">
            <div class="absolute top-2 right-2 h-4 w-8 bg-gray-300 rounded"></div>
          </div>
          <div class="flex items-center justify-between px-3 py-2.5 border-t border-gray-200">
            <div class="flex-1 min-w-0 space-y-1.5">
              <div class="h-3 bg-gray-200 rounded w-4/5"></div>
              <div class="h-2.5 bg-gray-100 rounded w-1/3"></div>
            </div>
            <div class="w-4 h-4 bg-gray-200 rounded ml-2 flex-shrink-0"></div>
          </div>
        </div>

        <div class="rounded-xl overflow-hidden border border-gray-200">
          <div class="relative w-full bg-gray-200" style="padding-top: 66.66%">
            <div class="absolute top-2 right-2 h-4 w-8 bg-gray-300 rounded"></div>
          </div>
          <div class="flex items-center justify-between px-3 py-2.5 border-t border-gray-200">
            <div class="flex-1 min-w-0 space-y-1.5">
              <div class="h-3 bg-gray-200 rounded w-3/5"></div>
              <div class="h-2.5 bg-gray-100 rounded w-2/5"></div>
            </div>
            <div class="w-4 h-4 bg-gray-200 rounded ml-2 flex-shrink-0"></div>
          </div>
        </div>

        <div class="rounded-xl overflow-hidden border border-gray-200">
          <div class="relative w-full bg-gray-200" style="padding-top: 66.66%">
            <div class="absolute top-2 right-2 h-4 w-8 bg-gray-300 rounded"></div>
          </div>
          <div class="flex items-center justify-between px-3 py-2.5 border-t border-gray-200">
            <div class="flex-1 min-w-0 space-y-1.5">
              <div class="h-3 bg-gray-200 rounded w-3/4"></div>
              <div class="h-2.5 bg-gray-100 rounded w-1/2"></div>
            </div>
            <div class="w-4 h-4 bg-gray-200 rounded ml-2 flex-shrink-0"></div>
          </div>
        </div>

        <div class="rounded-xl overflow-hidden border border-gray-200">
          <div class="relative w-full bg-gray-200" style="padding-top: 66.66%">
            <div class="absolute top-2 right-2 h-4 w-8 bg-gray-300 rounded"></div>
          </div>
          <div class="flex items-center justify-between px-3 py-2.5 border-t border-gray-200">
            <div class="flex-1 min-w-0 space-y-1.5">
              <div class="h-3 bg-gray-200 rounded w-2/3"></div>
              <div class="h-2.5 bg-gray-100 rounded w-2/5"></div>
            </div>
            <div class="w-4 h-4 bg-gray-200 rounded ml-2 flex-shrink-0"></div>
          </div>
        </div>

        <div class="rounded-xl overflow-hidden border border-gray-200">
          <div class="relative w-full bg-gray-200" style="padding-top: 66.66%">
            <div class="absolute top-2 right-2 h-4 w-8 bg-gray-300 rounded"></div>
          </div>
          <div class="flex items-center justify-between px-3 py-2.5 border-t border-gray-200">
            <div class="flex-1 min-w-0 space-y-1.5">
              <div class="h-3 bg-gray-200 rounded w-4/5"></div>
              <div class="h-2.5 bg-gray-100 rounded w-1/3"></div>
            </div>
            <div class="w-4 h-4 bg-gray-200 rounded ml-2 flex-shrink-0"></div>
          </div>
        </div>

        <div class="rounded-xl overflow-hidden border border-gray-200">
          <div class="relative w-full bg-gray-200" style="padding-top: 66.66%">
            <div class="absolute top-2 right-2 h-4 w-8 bg-gray-300 rounded"></div>
          </div>
          <div class="flex items-center justify-between px-3 py-2.5 border-t border-gray-200">
            <div class="flex-1 min-w-0 space-y-1.5">
              <div class="h-3 bg-gray-200 rounded w-3/5"></div>
              <div class="h-2.5 bg-gray-100 rounded w-2/4"></div>
            </div>
            <div class="w-4 h-4 bg-gray-200 rounded ml-2 flex-shrink-0"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer bar -->
    <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100 bg-gray-50">
      <div class="h-3 bg-gray-200 rounded w-32"></div>
      <div class="h-3 bg-gray-200 rounded w-24"></div>
    </div>
  </div>
</template>
