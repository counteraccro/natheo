<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de générer les events pour la sauvegarde des options systèmes et options users
 */

import axios from 'axios';
import { emitter } from '../../../../utils/useEvent';

export default {
  name: 'OptionSystem',
  props: {
    url_update: String,
  },
  mounted() {
    let change = this.OnChange;
    let elements = document.getElementsByClassName('event-input');
    for (let i = 0; i < elements.length; i++) {
      (function (index) {
        elements[index].addEventListener('change', change);
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
      let value = '';

      // Cas null si type n'existe pas
      switch (element.getAttribute('type')) {
        case 'text':
        case null:
          value = element.value;
          break;
        case 'checkbox':
          value = 0;
          if (element.checked) {
            value = 1;
          }
          break;
      }

      let required = element.getAttribute('required');

      if (required !== null) {
        if (!value) {
          element.classList.add('is-invalid');
          return false;
        } else {
          element.classList.remove('is-invalid');
        }
      }

      element.disabled = true;
      let spinner = document.getElementById('spinner-' + id);
      let help = document.getElementById('help-' + id);
      let success = document.getElementById('success-' + id);

      spinner.classList.remove('visually-hidden');

      axios
        .post(this.url_update, {
          key: id,
          value: value,
        })
        .then(function (response) {
          if (response.data.success) {
            help.classList.add('visually-hidden');
            success.classList.remove('visually-hidden');
            element.classList.add('is-valid');

            setTimeout(() => {
              element.classList.remove('is-valid');
              help.classList.remove('visually-hidden');
              success.classList.add('visually-hidden');
            }, 3000);
            element.disabled = false;
          } else {
            console.error(response.data.msg);
          }
          spinner.classList.add('visually-hidden');
        })
        .catch(function (error) {
          spinner.classList.add('visually-hidden');
          element.disabled = false;
          console.error(error);
        })
        .finally(() => {
          emitter.emit('reset-check-confirm');

          if (id === 'OU_THEME_SITE') {
            let stylesheet = document.querySelectorAll('[href*="/build/admin"]');
            let href = stylesheet[0].href;
            let tab = href.split('_');
            stylesheet[0].href = tab[0] + '_' + value + '.css';
          }
        });
    },
  },
};
</script>

<template></template>
