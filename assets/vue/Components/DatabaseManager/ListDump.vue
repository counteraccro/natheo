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
    translate: Object,
  },
  emits: [],
  data() {
    return {};
  },
  mounted() {},
  computed: {},
  methods: {},
};
</script>

<template>
  <h3 class="mt-4 mb-2">{{ this.translate.title }}</h3>
  <div class="table-responsive">
    <table class="table table-sm table-striped table-hover" aria-describedby="table">
      <thead>
        <tr>
          <th>
            {{ this.translate.file_name }}
          </th>
          <th>
            {{ this.translate.action }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="this.data.length" v-for="row in this.data">
          <td>
            {{ row.name }}
          </td>
          <td>
            <a :href="row.url" class="btn btn-secondary btn-sm"><i class="bi bi-download"></i></a>
          </td>
        </tr>
        <tr v-else>
          <td colspan="2" class="text-center">
            <i>{{ this.translate.no_data }}</i>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped></style>
