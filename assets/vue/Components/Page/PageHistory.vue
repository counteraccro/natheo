<script>
/**
 * Contenu de l'onglet historique
 * @author Gourdon Aymeric
 * @version 1.0
 */

export default {
  name: 'PageHistory',
  props: {
    history: Object,
    translate: Object,
  },
  emits: ['reload-page-history'],
  data() {
    return {};
  },
  mounted() {},
  computed: {},
  methods: {},
};
</script>

<template>
  <div id="content-history" class="overflow-auto" style="height: 600px">
    <h5>{{ this.translate.title }}</h5>
    <div>{{ this.translate.description }}</div>
    <hr />

    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>{{ this.translate.id }}</th>
          <th>{{ this.translate.time }}</th>
          <th>{{ this.translate.user }}</th>
          <th>{{ this.translate.action }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="this.history.length > 0" v-for="history in this.history">
          <td>#{{ history.id + 1 }}</td>
          <td v-html="history.time"></td>
          <td>{{ history.user }}</td>
          <td>
            <div class="btn btn-secondary btn-sm" @click="$emit('reload-page-history', history.id)">
              <i class="bi bi-arrow-clockwise"></i> {{ this.translate.reload }}
            </div>
          </td>
        </tr>
        <tr v-else>
          <td colspan="4" class="text-center text-bg-secondary">{{ this.translate.empty }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
