<script>
import {PageRender} from "../../../../../../utils/Front/Const/PageRender";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Content page
 */
export default {
  name: 'ContentStructure',
  computed: {
    PageRender() {
      return PageRender
    }
  },
  props: {
    page: Object
  },
  emits: [],
  data() {
    return {
      value: 'Value',
    }
  },
  mounted() {

  },

  methods: {}
}
</script>

<template>
  <div v-if="this.page.render === PageRender.oneBlock">
    One block
  </div>
  <div v-if="this.page.render === PageRender.twoBlock">
    Two block
  </div>
  <div v-if="this.page.render === PageRender.threeBlock">
    3 block
  </div>
  <div v-if="this.page.render === PageRender.twoBlockBottom">
    two block bottom
  </div>
  <div v-if="this.page.render === PageRender.threeBlockBottom">
    3 block bottom
  </div>
  <div v-if="this.page.render === PageRender.oneTwoBlock">
    <div class="grid grid-cols-12">
      <div class="col-span-12 lg:order-1">
        block dessus
      </div>
      <div class="col-span-12 lg:col-span-6 order-2">
        <!--<component is="">

        </component>-->
      </div>
      <div class="col-span-12 lg:col-span-6 order-3">
        block dessous 2
      </div>
    </div>
  </div>
  <div v-if="this.page.render === PageRender.twoOneBlock">
    2 bloc au dessus, 1 en dessous
  </div>
  <div v-if="this.page.render === PageRender.twoTwoBlock">
    2 bloc au dessus, 2 block en dessous
  </div>

  <div class="grid grid-cols-12">

  </div>
</template>