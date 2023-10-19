<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Génération du tableau GRID
 */

export default {
  props: {
    data: Array,
    columns: Array,
    filterKey: String,
    sortOrders: Object,
    translate: Object,
  },
  emits: ['redirect-action'],
  data() {
    return {
      sortKey: '',
    }
  },
  computed: {

    /**
     * Permet de filtrer les données présents dans data
     * @returns {*[]}
     */
    filteredData() {

      const sortKey = this.sortKey
      const filterKey = this.filterKey && this.filterKey.toLowerCase()
      const order = this.sortOrders[sortKey] || 1
      let data = this.data
      if (filterKey) {
        data = data.filter((row) => {
          return Object.keys(row).some((key) => {
            return String(row[key]).toLowerCase().indexOf(filterKey) > -1
          })
        })
      }
      if (sortKey) {
        data = data.slice().sort((a, b) => {
          a = a[sortKey]
          b = b[sortKey]
          return (a === b ? 0 : a > b ? 1 : -1) * order
        })
      }
      return data
    }
  },
  methods: {

    /**
     * Permet de trier en fonction d'une clé
     * @param key
     */
    sortBy(key) {
      this.sortKey = key
      this.sortOrders[key] = this.sortOrders[key] * -1
    },
    capitalize(str) {
      return str.charAt(0).toUpperCase() + str.slice(1)
    },

    /**
     * Converti un json en tableau
     * @param json
     * @returns {any|boolean}
     */
    jsonParse(json) {
      let tab = JSON.parse(json);
      if (tab[0] === "") {
        return false;
      }
      return tab;
    },

    highlightSearch(text) {
      if (!this.filterKey) {
        return text;
      }
      return text.toString().replace(new RegExp(this.filterKey, "gi"), match => {
        return '<span class="bg-secondary text-white p-1">' + match + '</span>';
      });
    }
  }
}
</script>

<template>
  <table class="table table-striped table-hover align-middle" v-if="filteredData.length" aria-label="Table-grid">
    <thead>
    <tr>
      <th v-for="key in columns"
          @click="sortBy(key)"
          :class="{ active: sortKey === key }">
        {{ capitalize(key) }}
        <span class="bi" :class="sortOrders[key] > 0 ? 'bi-caret-down-fill' : 'bi-caret-up-fill'">
          </span>
      </th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="entry in filteredData">
      <td v-for="key in columns">
        <span v-if="key !== 'action'" v-html="this.highlightSearch(entry[key])"></span>
        <div v-else>
          <button class="btn btn-secondary btn-sm m-1" v-for="data in this.jsonParse(entry[key])"
                  @click="$emit('redirect-action', data.url, data.ajax, data.confirm, data.msgConfirm)"
                  v-html="data.label">
          </button>
        </div>
      </td>
    </tr>
    </tbody>
  </table>
  <div v-else>
    <p class="text-center"><i class="bi bi-search"></i> <i>{{ translate.noresult }}</i></p>
  </div>
</template>
