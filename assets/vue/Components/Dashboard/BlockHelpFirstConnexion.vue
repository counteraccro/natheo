<script>/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant card help première connexion
 */

export default {
  name: "BlockHelpFirstConnexion",
  components: {},
  emit: [],
  props: {
    urls: Object,
    translate: Object,
    datas: Object,
  },
  data() {
    return {
      loading: false
    }
  },
  mounted() {

  },
  methods: {}
}
</script>

<template>
      <div class="card">
        <h5 class="card-header"><i class="bi bi-info-circle"></i> {{ this.translate.title }} </h5>

        <div class="card-body" v-if="this.loading">
          <div class="spinner-border spinner-border-sm text-secondary" role="status">
            <span class="visually-hidden">{{ this.translate.loading }}</span>
          </div> {{ this.translate.loading }}
        </div>

        <div class="card-body" v-else>
          <h5 class="card-title"> {{ this.translate.sub_title }}</h5>
          <p class="card-text">{{ this.translate.text_1 }}</p>
          <ul class="list-group mb-2">
            <li class="list-group-item list-group-item-warning"><i class="bi bi-exclamation-circle"></i> {{ this.translate.option_1 }}</li>
            <li class="list-group-item list-group-item-warning"><i class="bi bi-exclamation-circle"></i> {{ this.translate.option_2 }}</li>
            <li class="list-group-item list-group-item-warning"><i class="bi bi-exclamation-circle"></i> {{ this.translate.option_3 }}</li>
            <li class="list-group-item list-group-item-success"><i class="bi bi-check-circle"></i> aaaa</li>
          </ul>

          <p class="card-text">{{ this.translate.text_end_success }}</p>

          <div class="float-end">
            <div class="btn btn-secondary btn-sm me-2"><i class="bi bi-arrow-clockwise"></i></div>
            <div class="btn btn-secondary btn-sm">{{ this.translate.btn_def_hide }}</div>
          </div>
        </div>

      </div>
</template>