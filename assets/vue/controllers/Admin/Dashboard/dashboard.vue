<script>/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant left ou right pour la création /edition d'un menu
 */

export default {
  name: "Dashboard",
  components: {},
  emit: [],
  props: {
    urls: Object,
    translate: Object,
    datas: Object,
  },
  data() {
    return {}
  },
  mounted() {

  },
  methods: {}
}
</script>

<template>
  <div class="row">
    <div class="col-6 mb-3">
      <div class="card">
        <h5 class="card-header"><i class="bi bi-activity"></i> {{ this.translate.dashboard_flux_activity }} </h5>
        <div class="card-body">
          <h5 class="card-title">Special title treatment</h5>
          <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
          <a href="#" class="btn btn-primary">Go somewhere</a>
        </div>
      </div>
    </div>

    <div class="col-6 mb-3">
      <div class="card">
        <h5 class="card-header"><i class="bi bi-chat-left-text"></i> {{ this.translate.dashboard_last_comment }} </h5>
        <div class="card-body">
          <h5 class="card-title">Special title treatment</h5>
          <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
          <a href="#" class="btn btn-primary">Go somewhere</a>
        </div>
      </div>
    </div>

    <div class="col-6 mb-3">
      <div class="card">
        <h5 class="card-header"><i class="bi bi-chat-left-text"></i> {{ this.translate.dashboard_last_comment }} </h5>
        <div class="card-body">
          <h5 class="card-title">Special title treatment</h5>
          <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
          <a href="#" class="btn btn-primary">Go somewhere</a>
        </div>
      </div>
    </div>

    <div class="col-6 mb-3">
      <div class="card">
        <h5 class="card-header"><i class="bi bi-chat-left-text"></i> {{ this.translate.dashboard_last_comment }} </h5>
        <div class="card-body">
          <h5 class="card-title">Special title treatment</h5>
          <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
          <a href="#" class="btn btn-primary">Go somewhere</a>
        </div>
      </div>
    </div>
  </div>

</template>