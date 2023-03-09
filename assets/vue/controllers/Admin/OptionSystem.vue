<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de générer les events pour la sauvegarde des options systèmes
 */

import axios from 'axios'

export default {
  name: "OptionSystem",
  props: {
    url_update: String,
  },
  mounted() {

    let change = this.OnChange;
    let elements = document.getElementsByClassName('event-input');
    for (let i = 0; i < elements.length; i++) {
      (function (index) {
        elements[index].addEventListener("change", change)
      })(i);
    }

  },
  methods: {
    /**
     * Au changement d'un input, verification de la donnée et enregistrement de celle ci
     * @param event
     * @returns {boolean}
     * @constructor
     */
    OnChange(event) {

      let id = event.target.getAttribute('id');
      let value = event.target.value;
      let required = event.target.getAttribute('required');

      if (required !== null) {
        if (!value) {
          event.target.classList.add('is-invalid');
          return false;
        } else {
          event.target.classList.remove('is-invalid');
        }
      }

      axios.post(this.url_update, {
        key: id,
        value: value
      })
          .then(function (response) {
            console.log(response);
          })
          .catch(function (error) {
            console.log(error);
          });
    }
  }
}
</script>

<template>

</template>

<style scoped>

</style>