<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant footer pour la création /edition d'un menu
 */

export default {
  name: "MenuFooter",
  components: {},
  emit: [],
  props: {
    menu: Object,
    type: String,
    data: Object
  },
  watch: {
    type: 'switchType'
  },
  data() {
    return {

    }
  },
  mounted() {

  },
  methods: {

    /**
     * Permet de déterminer le type de rendu
     */
    switchType() {
        switch (this.type) {
          case '13' :
            console.log('TYPE_FOOTER_1_COLONNE');
            break;
          case '14' :
            console.log('TYPE_FOOTER_2_COLONNE');
            break;
          case '15' :
            console.log('TYPE_FOOTER_3_COLONNE');
            break;
          case '16' :
            console.log('TYPE_FOOTER_4_COLONNE');
            break;
        }
    }
  }
}
</script>

<template>

  <div>Menu footer {{this.type}}</div>

</template>