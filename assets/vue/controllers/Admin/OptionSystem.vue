<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de générer les events pour la sauvegarde des options systèmes
 */


import axios from 'axios'
import VueAxios from 'vue-axios'

export default {
  name: "OptionSystem",
  mounted() {

    let change = this.OnChange;
    let elements = document.getElementsByClassName('event-input');
    for(let i = 0; i < elements.length; i++) {
      (function(index) {
        elements[index].addEventListener("change", change)
      })(i);
    }

  },
  methods: {
    OnChange(event) {

      let id = event.target.getAttribute('id');
      alert(id);

      //this.id = event.target.getAttribute('data-id');
      //this.dataName = event.target.getAttribute('data-name');
    }
  }
}
</script>

<template>

</template>

<style scoped>

</style>