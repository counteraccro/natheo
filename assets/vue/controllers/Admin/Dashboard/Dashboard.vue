<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Vue rendu du dashboard
 */
import { defineComponent, type PropType } from 'vue';
import BlockHelpFirstConnexion from '../../../Components/Dashboard/BlockHelpFirstConnexion.vue';
import BlockLastComment from '../../../Components/Dashboard/BlockLastComment.vue';
import BlockLastPage from '../../../Components/Dashboard/BlockLastPage.vue';
import BlockPageMostViewed from '@/vue/Components/Dashboard/BlockPageMostViewed.vue';
import type { DashboardUrls, DashboardTranslate, DashboardDatas, DashboardRoles } from '@/ts/Dashboard/Dashboard.type';

export default defineComponent({
  name: 'Dashboard',
  components: { BlockPageMostViewed, BlockLastPage, BlockLastComment, BlockHelpFirstConnexion },
  props: {
    urls: {
      type: Object as PropType<DashboardUrls>,
      required: true,
    },
    translate: {
      type: Object as PropType<DashboardTranslate>,
      required: true,
    },
    datas: {
      type: Object as PropType<DashboardDatas>,
      required: true,
    },
    roles: {
      type: Object as PropType<DashboardRoles>,
      required: true,
    },
  },
  emits: [],
  data() {
    return {
      showBlockHelpFirstConnexion: true as boolean,
    };
  },
  mounted() {},
  methods: {
    /**
     * Rechargement du grid
     */
    reloadGrid(): void {},

    /**
     * Masque le bloc de première connexion
     */
    hideBlockFistConnexion(): void {
      this.showBlockHelpFirstConnexion = false;
    },
  },
});
</script>

<template>
  <div id="grid-block-dashboard" class="row">
    <div
      class="grid grid-cols-1 gap-6 mb-6"
      v-if="datas.dashboard_help_first_connexion.help_first_connexion && showBlockHelpFirstConnexion"
    >
      <block-help-first-connexion
        :translate="translate.dashboard_help_first_connexion"
        :datas="datas.dashboard_help_first_connexion"
        :urls="urls.dashboard_help_first_connexion"
        @reload-grid="reloadGrid"
        @hide-block="hideBlockFistConnexion"
      />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <block-last-comment
        v-if="roles.isContributeur"
        :translate="translate.dashboard_last_comments"
        :urls="urls.dashboard_last_comments"
        @reload-grid="reloadGrid"
      />
      <block-page-most-viewed
        v-if="roles.isContributeur"
        :translate="translate.dashboard_page_most_viewed"
        :urls="urls.dashboard_page_most_viewed"
        @reload-grid="reloadGrid"
      />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <block-last-page
        v-if="roles.isContributeur"
        :translate="translate.dashboard_last_pages"
        :urls="urls.dashboard_last_pages"
        @reload-grid="reloadGrid"
      />
    </div>
  </div>
</template>
