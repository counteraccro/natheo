<script>
import NavMegaMenu from "./NavMegaMenu.vue";
import Menu from "../../../../controllers/Admin/Content/Menu/Menu.vue";
import {MenuType} from "../../../../../utils/Front/Const/Menu";

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Structure de la page de front
 */
export default {
  name: 'Nav',
  computed: {
    MenuType() {
      return MenuType
    }
  },
  components: {NavMegaMenu},
  props: {
    optionsSystem: Object,
    data: Object,
    utilsFront: Object
  },
  emits: [],
  data() {
    return {
      value: 'Value',
    }
  },
  mounted() {

  },

  methods: {

  }
}
</script>

<template>
  <nav-mega-menu v-if="this.data.type === MenuType.headerBigMenu"
      :options-system="this.optionsSystem"
      :data="this.data"
      :utils-front="this.utilsFront"
  />
  <div v-else>
    a faire
  </div>
</template>