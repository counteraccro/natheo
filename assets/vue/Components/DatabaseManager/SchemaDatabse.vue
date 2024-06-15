<script>

/**
 * Retourne le schema de la base de données
 * @author Gourdon Aymeric
 * @version 1.0
 */

export default {
  name: 'schemaDatabase',
  components: {},
  props: {
    data: Object,
    translate : Object
  },
  emits: ['load-schema-table'],
  data() {
    return {}
  },
  mounted() {

  },
  computed: {},
  methods: {}
}

</script>

<template>

  <h3 class="mt-4 mb-2">{{ this.translate.title }}</h3>
  <div class="table-responsive">
    <table class="table table-sm table-striped table-hover" aria-describedby="table">
      <thead>
      <tr>
        <th v-for="(header, key) in this.data.header">
          {{ header }}
        </th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="row in this.data.result">
        <td v-for="(header, key) in this.data.header">
          <a v-if="key === 'table_name'" class="link-underline-primary" style="cursor: pointer" @click="this.$emit('load-schema-table', row[key]);">{{ row[key] }}</a>
          <span v-else>{{ row[key] }}</span>
        </td>
      </tr>
      </tbody>
      <tfoot>
      <tr class="table-secondary" v-if="!this.data.length">
        <th>{{ this.translate.nb_row_total }} :</th>
        <th>{{ this.data.stat.nbTable }}</th>
        <th>{{ this.data.stat.nbElement }}</th>
        <th>{{ this.data.stat.sizeBite }}</th>
      </tr>
      </tfoot>
    </table>
  </div>

</template>