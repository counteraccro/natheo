<script>

/**
 * Retourne le schema de la base de données
 * @author Gourdon Aymeric
 * @version 1.0
 */

export default {
  name: 'schemaTable',
  components: {},
  props: {
    data: Object,
    translate : Object
  },
  emits: [],
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
  <h3 class="mt-4 mb-2">{{ this.translate.title }} : <b>{{ this.data.table }}</b></h3>
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
          {{ row[key] }}
        </td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>

</style>