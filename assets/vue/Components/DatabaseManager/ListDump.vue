<script>

/**
 * Retourne la liste des dumps
 * @author Gourdon Aymeric
 * @version 1.0
 */

export default {
  name: 'ListDump',
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
  <h3 class="mt-4 mb-2">{{ this.translate.title }}</h3>
  <div class="table-responsive">
    <table class="table table-sm table-striped table-hover" aria-describedby="table">
      <thead>
      <tr>
        <th>
          data
        </th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="row in this.data.result">
        <td>TD</td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>

</style>