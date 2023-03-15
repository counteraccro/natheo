<script>
export default {
  props: {
    data: Array,
    columns: Array,
    filterKey: String,
    sortOrders: Object,
  },
  emits: ['redirect-action'],
  data() {
    return {
      sortKey: '',
    }
  },
  computed: {
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
    }
  }
}
</script>

<template>
  <table class="table table-striped table-hover" v-if="filteredData.length">
    <thead>
    <tr>
      <th v-for="key in columns"
          @click="sortBy(key)"
          :class="{ active: sortKey == key }">
        {{ capitalize(key) }}
        <span class="bi" :class="sortOrders[key] > 0 ? 'bi-caret-up-fill' : 'bi-caret-down-fill'">
          </span>
      </th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="entry in filteredData">
      <td v-for="key in columns">
        <span v-if="key !== 'action'" v-html="entry[key]"></span>
        <div v-else>
          <button class="btn btn-secondary btn-sm m-1" v-for="data in this.jsonParse(entry[key])" @click="$emit('redirect-action', data.url, data.id, data.ajax)" v-html="data.label">
          </button>
        </div>
      </td>
    </tr>
    </tbody>
  </table>
  <p v-else>No matches found.</p>
</template>

<style>
</style>