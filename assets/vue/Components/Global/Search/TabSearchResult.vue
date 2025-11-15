<script>
/**
 * Permet d'afficher la préview du footer
 * @author Gourdon Aymeric
 * @version 1.0
 */
import SearchPaginate from './SearchPaginate.vue';

export default {
  name: 'TabSearchResult',
  components: { SearchPaginate },
  props: {
    result: Object,
    paginate: Object,
    translate: Object,
    translatePaginate: Object,
    entity: String,
  },
  emits: ['change-page-event'],
  data() {
    return {};
  },
  mounted() {},
  methods: {
    changePageEvent(page, limit) {
      this.$emit('change-page-event', this.entity, page, limit);
    },
  },
};
</script>

<template>
  <div v-for="row in this.result.elements" class="card rounded-lg flex mb-3 p-6 gap-2.5">
    <div class="w-32">
      <img v-if="row.img" :src="row.img" class="max-w-xs h-20 aspect-3/2 object-cover rounded" alt="img" />
      <div
        v-else
        class="m-auto w-[7.5rem] h-20 flex items-center justify-center bg-[var(--primary-lighter)] rounded-lg"
      >
        <svg
          class="w-10 h-10"
          style="color: var(--primary)"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          fill="none"
          viewBox="0 0 24 24"
        >
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-width="2"
            d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
          />
        </svg>
      </div>
    </div>
    <div>
      <h3 class="text-lg font-semibold mb-0.5 text-[var(--text-primary)]" v-html="row.label"></h3>
      <p v-if="row.contents[0]" class="text-sm text-[var(--text-secondary)] font-bold">
        {{ this.translate.content }}
      </p>
      <p
        v-if="row.contents[0]"
        class="text-[var(--text-secondary)] flex items-center gap-1 text-sm"
        v-for="content in row.contents"
      >
        <svg
          class="icon-sm"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          fill="none"
          viewBox="0 0 24 24"
        >
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 12H5m14 0-4 4m4-4-4-4"
          />
        </svg>
        <span v-html="content"></span>
      </p>
      <p v-else class="text-sm text-[var(--text-secondary)]">{{ this.translate.noResultContent }}</p>

      <p class="text-[var(--text-light)] flex gap-2.5 mt-4 text-xs">
        <span>{{ this.translate.create }} {{ row.date.create }} </span>
        <span>{{ this.translate.update }} {{ row.date.update }}</span>
        <span v-if="row.author"> {{ this.translate.author }} <span v-html="row.author"></span></span>
      </p>
    </div>
    <div class="ml-auto">
      <a v-for="(url, key) in row.urls" :href="url" target="_blank" class="btn btn-secondary btn-icon btn-sm me-2">
        <svg
          v-if="key === 'edit'"
          class="icon-sm"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <path
            stroke="currentColor"
            stroke-width="2"
            d="M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28"
          ></path>
        </svg>

        <svg v-if="key === 'preview'" class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
          ></path>
        </svg>
      </a>
    </div>
  </div>

  <search-paginate
    :nb-elements="this.paginate.limit"
    :current-page="this.paginate.current"
    :nb-elements-total="this.result.total"
    :translate="this.translatePaginate"
    @change-page-event="changePageEvent"
  >
  </search-paginate>
</template>
