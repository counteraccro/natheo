<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Génération du tableau GRID
 */
import Page from '@/vue/controllers/Admin/Content/Page/Page.vue';

export default {
  components: { Page },
  props: {
    data: Array,
    columns: Array,
    filterKey: String,
    sortOrders: Object,
    translate: Object,
    searchMode: String,
  },
  emits: ['redirect-action', 'sort-action'],
  data() {
    return {
      sortKey: '',
    };
  },
  computed: {
    /**
     * Permet de filtrer les données présents dans data
     * @returns {*[]}
     */
    filteredData() {
      const sortKey = this.sortKey;
      const filterKey = this.filterKey && this.filterKey.toLowerCase();
      const order = this.sortOrders[sortKey] || 1;
      let data = this.data;
      if (filterKey && this.isCanSearch()) {
        data = data.filter((row) => {
          return Object.keys(row).some((key) => {
            return String(row[key]).toLowerCase().indexOf(filterKey) > -1;
          });
        });
      }

      /*if (sortKey) {
        data = data.slice().sort((a, b) => {
          a = a[sortKey];
          b = b[sortKey];
          return (a === b ? 0 : a > b ? 1 : -1) * order;
        });
      }*/
      return data;
    },
  },
  methods: {
    /**
     * Détermine si on peut rechercher ou non
     * @returns {boolean}
     */
    isCanSearch() {
      return this.searchMode === 'table';
    },

    /**
     * Permet de trier en fonction d'une clé
     * @param key
     */
    sortBy(key) {
      this.sortKey = key;
      this.sortOrders[key] = this.sortOrders[key] * -1;
      this.$emit('sort-action', this.sortKey, this.sortOrders[key]);
    },

    capitalize(str) {
      return str.charAt(0).toUpperCase() + str.slice(1);
    },

    /**
     * Converti un json en tableau
     * @param json
     * @returns {any|boolean}
     */
    jsonParse(json) {
      let tab = JSON.parse(json);
      if (tab[0] === '') {
        return false;
      }
      return tab;
    },

    highlightSearch(text) {
      if (text === undefined) {
        return '';
      }

      if (!this.filterKey || !this.isCanSearch()) {
        return text;
      }
      return text.toString().replace(new RegExp(this.filterKey, 'gi'), (match) => {
        return '<span class="bg-[var(--primary)] text-white p-1 ps-3 pe-3 rounded ">' + match + '</span>';
      });
    },
  },
};
</script>

<template>
  <div class="card rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full" v-if="filteredData.length">
        <thead class="bg-[var(--bg-main)]">
          <tr>
            <th
              class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-[var(--text-secondary)]"
              v-for="key in columns"
              @click="key !== 'action' ? sortBy(key) : ''"
            >
              <div class="flex items-center justify-center">
                {{ capitalize(key) }}

                <svg
                  v-if="key !== 'action' && this.sortOrders[key]"
                  class="w-3 h-3 cursor-pointer"
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
                    :d="sortOrders[key] > 0 ? 'm8 10 4 4 4-4' : 'm16 14-4-4-4 4'"
                  />
                </svg>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[var(--border-color)]">
          <tr class="bg-[var(--bg-card)] hover:bg-[var(--bg-hover)]" v-for="entry in filteredData">
            <td
              class="px-3 py-1 text-sm text-[var(--text-secondary)] text-center"
              v-for="key in columns"
              :class="entry.isDisabled && key !== 'action' ? 'opacity-25' : ''"
            >
              <span v-if="key !== 'action'" v-html="this.highlightSearch(entry[key])"></span>
              <div v-else>
                <button
                  class="btn btn-icon m-1 btn-xs"
                  :class="'btn-ghost-' + data.color"
                  v-for="data in entry[key]"
                  @click="$emit('redirect-action', data.url, data.ajax, data.confirm, data.msgConfirm, data.type)"
                >
                  <svg
                    class="icon-sm"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <path
                      v-if="typeof data.label[0] != 'undefined'"
                      stroke="currentColor"
                      stroke-width="2"
                      :d="data.label[0]"
                    ></path>
                    <path
                      v-if="typeof data.label[1] != 'undefined'"
                      stroke="currentColor"
                      stroke-width="2"
                      :d="data.label[1]"
                    ></path>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-else class="flex items-center justify-center p-4 text-[var(--text-primary)]">
        <svg
          class="w-6 h-6 me-2"
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
        <i>{{ translate.noresult }}</i>
      </div>
    </div>
  </div>
</template>
