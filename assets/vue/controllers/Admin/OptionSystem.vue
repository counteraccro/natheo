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

      let element = event.target;

      let id = element.getAttribute('id');
      let value = element.value;
      let required = element.getAttribute('required');

      if (required !== null) {
        if (!value) {
          element.classList.add('is-invalid');
          return false;
        } else {
          element.classList.remove('is-invalid');
        }
      }

      let spinner = document.getElementById('spinner-' + id);
      spinner.classList.remove('visually-hidden');

      axios.post(this.url_update, {
        key: id,
        value: value
      }).then(function (response) {
        spinner.classList.add('visually-hidden');
        element.classList.add('is-valid');

        setTimeout(() => {
          element.classList.remove('is-valid');
        }, 3000)

      }).catch(function (error) {
        spinner.classList.add('visually-hidden');
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