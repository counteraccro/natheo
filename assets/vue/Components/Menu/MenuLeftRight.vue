<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant left ou right pour la création /edition d'un menu
 */

export default {
  name: "MenuLeftRight",
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
        case '11' :
          console.log('TYPE_LEFT_RIGHT_SIDE_BAR');
          break;
        case '12' :
          console.log('TYPE_LEFT_RIGHT_SIDE_BAR_ACCORDEON');
          break;
      }
    }
  }
}
</script>

<template>

  <div>Menu left right</div>

</template>