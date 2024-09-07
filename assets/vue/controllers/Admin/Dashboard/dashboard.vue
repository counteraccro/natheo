<script>/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Vue rendu du dashboard
 */
import BlockHelpFirstConnexion from "../../../Components/Dashboard/BlockHelpFirstConnexion.vue";
import Masonry from "masonry-layout";

export default {
  name: "Dashboard",
  components: {BlockHelpFirstConnexion},
  emit: [],
  props: {
    urls: Object,
    translate: Object,
    datas: Object,
  },
  data() {
    return {
      masonry : null
    }
  },
  mounted() {
    this.masonry = new Masonry('#grid-block-dashboard', {"percentPosition": true});
    //this.masonry.layout();
  },
  methods: {

    reloadGrid()
    {
      console.log('reload');

      this.masonry.layout();
      //console.log(masonry);
    }

  }
}
</script>

<template>
  <div id="grid-block-dashboard" class="row">

    <div class="col-6 mb-3" v-if="this.datas.dashboard_help_first_connexion.help_first_connexion">
      <block-help-first-connexion
          :translate="this.translate.dashboard_help_first_connexion"
          :datas="this.datas.dashboard_help_first_connexion"
          :urls="this.urls.dashboard_help_first_connexion"
          @reload-grid="this.reloadGrid"
      />
    </div>

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