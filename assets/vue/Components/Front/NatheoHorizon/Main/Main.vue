<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Contenu principal : menus verticaux éventuels et contenu de la page
 */
import { defineComponent, type PropType } from 'vue';
import VerticalMenu from './VerticalMenu.vue';
import ContentStructure from './Content/ContentStructure.vue';
import type { FrontApi } from '@/utils/Front/FrontApi';
import type { UtilsFront } from '@/utils/Front/UtilsFront';
import type { FrontApiError, FrontMainTranslate, FrontPage } from '@/ts/Front/NatheoHorizon/Structure.type';

export default defineComponent({
  name: 'Main',
  components: { ContentStructure, VerticalMenu },
  props: {
    utilsFront: {
      type: Object as PropType<UtilsFront>,
      required: true,
    },
    page: {
      type: Object as PropType<FrontPage>,
      required: true,
    },
    frontApi: {
      type: Object as PropType<FrontApi>,
      required: true,
    },
    translate: {
      type: Object as PropType<FrontMainTranslate>,
      required: true,
    },
    locale: {
      type: String,
      required: true,
    },
  },
  emits: {
    'api-failure': (_error: FrontApiError) => true,
  },
  computed: {
    contentClass(): string {
      if (this.page.menus?.LEFT && this.page.menus?.RIGHT) {
        return 'lg:col-span-8 lg:order-2 order-3';
      }
      return this.page.menus?.LEFT ? 'lg:col-span-10 lg:order-2' : 'lg:col-span-10 lg:order-1 order-3';
    },
  },
  methods: {
    apiFailure(error: FrontApiError): void {
      this.$emit('api-failure', error);
    },
  },
});
</script>

<template>
  <div class="grid grid-cols-12 gap-2 mt-2">
    <div v-if="page.menus?.LEFT" class="col-span-12 lg:col-span-2 lg:order-1 rounded-2xl mr-1">
      <VerticalMenu :utils-front="utilsFront" :menu="page.menus.LEFT" />
    </div>
    <div class="col-span-12" :class="contentClass">
      <ContentStructure
        :page="page"
        :front-api="frontApi"
        :translate="translate"
        :locale="locale"
        :utils-front="utilsFront"
        @api-failure="apiFailure"
      />
    </div>
    <div v-if="page.menus?.RIGHT" class="col-span-12 lg:col-span-2 lg:order-3 order-2 rounded-2xl ml-1">
      <VerticalMenu :utils-front="utilsFront" :menu="page.menus.RIGHT" />
    </div>
    <div v-else-if="!page.menus?.LEFT" class="col-span-12 lg:col-span-2 lg:order-3 order-2"></div>
  </div>
</template>
