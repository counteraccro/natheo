<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant header pour la création /edition d'un menu
 */

export default {
  name: "MenuHeader",
  components: {},
  emit: [],
  props: {
    menu: Object,
    type: Number,
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
        case 1 :
          console.log('TYPE_HEADER_SIDE_BAR');
          break;
        case 2 :
          console.log('TYPE_HEADER_MENU_DEROULANT');
          break;
        case 3 :
          console.log('TYPE_HEADER_MENU_DEROULANT_1_LIGNE_1_COLONNE');
          break;
        case 4 :
          console.log('TYPE_HEADER_MENU_DEROULANT_1_LIGNE_2_COLONNES');
          break;
        case 5 :
          console.log('TYPE_HEADER_MENU_DEROULANT_1_LIGNE_3_COLONNES');
          break;
        case 6 :
          console.log('TYPE_HEADER_MENU_DEROULANT_1_LIGNE_4_COLONNES');
          break;
        case 7 :
          console.log('TYPE_HEADER_MENU_DEROULANT_2_LIGNES_1_COLONNE');
          break;
        case 8 :
          console.log('TYPE_HEADER_MENU_DEROULANT_2_LIGNES_2_COLONNES');
          break;
        case 9 :
          console.log('TYPE_HEADER_MENU_DEROULANT_2_LIGNES_3_COLONNES');
          break;
        case 10 :
          console.log('TYPE_HEADER_MENU_DEROULANT_2_LIGNES_4_COLONNES');
          break;
      }
    }
  }
}
</script>

<template>

  <div>Menu Menu header</div>

</template>