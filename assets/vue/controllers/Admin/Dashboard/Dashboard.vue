<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Vue rendu du dashboard
 */
import BlockHelpFirstConnexion from '../../../Components/Dashboard/BlockHelpFirstConnexion.vue';
import BlockLastComment from '../../../Components/Dashboard/BlockLastComment.vue';
import BlockLastPage from '../../../Components/Dashboard/BlockLastPage.vue';

export default {
  name: 'Dashboard',
  components: { BlockLastPage, BlockLastComment, BlockHelpFirstConnexion },
  emit: [],
  props: {
    urls: Object,
    translate: Object,
    datas: Object,
    roles: Object,
  },
  data() {
    return {
      showBlockHelpFirstConnexion: true,
    };
  },
  mounted() {},
  methods: {
    /**
     * Rechargement du grid
     */
    reloadGrid() {},

    /**
     * Masque le bloc de première connexion
     */
    hideBlockFistConnexion() {
      this.showBlockHelpFirstConnexion = false;
    },
  },
};
</script>

<template>
  <div id="grid-block-dashboard" class="row">
    <div
      class="grid grid-cols-1 gap-6 mb-6"
      v-if="this.datas.dashboard_help_first_connexion.help_first_connexion && this.showBlockHelpFirstConnexion"
    >
      <block-help-first-connexion
        :translate="this.translate.dashboard_help_first_connexion"
        :datas="this.datas.dashboard_help_first_connexion"
        :urls="this.urls.dashboard_help_first_connexion"
        @reload-grid="this.reloadGrid"
        @hide-block="this.hideBlockFistConnexion"
      />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <block-last-comment
        v-if="this.roles.isContributeur"
        :translate="translate.dashboard_last_comments"
        :urls="urls.dashboard_last_comments"
        @reload-grid="reloadGrid"
      />
      <block-last-page
        v-if="this.roles.isContributeur"
        :translate="this.translate.dashboard_last_pages"
        :urls="this.urls.dashboard_last_pages"
        @reload-grid="this.reloadGrid"
      />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <block-last-comment
        v-if="this.roles.isContributeur"
        :translate="this.translate.dashboard_last_comments"
        :urls="this.urls.dashboard_last_comments"
        @reload-grid="this.reloadGrid"
      />
    </div>
  </div>
</template>
