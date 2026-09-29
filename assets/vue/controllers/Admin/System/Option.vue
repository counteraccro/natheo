<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Permet de générer les events pour la sauvegarde des options systèmes et options users
 */

import { defineComponent } from 'vue';
import axios from 'axios';
import { emitter } from '@/utils/useEvent';
import type { OptionField, OptionUpdatePayload, OptionUpdateResponse } from '@/ts/Option/Option.type';

export default defineComponent({
  name: 'OptionSystem',
  props: {
    url_update: {
      type: String,
      required: true,
    },
    csrf_token: {
      type: String,
      required: true,
    },
  },
  mounted() {
    const elements = document.getElementsByClassName('event-input');
    for (const element of Array.from(elements)) {
      element.addEventListener('change', this.onChange);
    }
  },
  beforeUnmount() {
    const elements = document.getElementsByClassName('event-input');
    for (const element of Array.from(elements)) {
      element.removeEventListener('change', this.onChange);
    }
  },
  methods: {
    /**
     * Au changement d'un input, verification de la donnée et enregistrement de celle ci
     * @param event
     */
    onChange(event: Event): void {
      const element = event.target as OptionField;
      const id = element.id;
      let value: string | number = '';

      // Cas null si type n'existe pas (select, textarea)
      switch (element.getAttribute('type')) {
        case 'text':
        case null:
          value = element.value;
          break;
        case 'checkbox':
          value = (element as HTMLInputElement).checked ? 1 : 0;
          element.classList.toggle('active');
          break;
      }

      const error = document.getElementById('error-' + id);
      if (element.hasAttribute('required') && !value) {
        element.classList.add('is-invalid');
        error?.classList.remove('hidden');
        return;
      }
      element.classList.remove('is-invalid');
      error?.classList.add('hidden');

      element.disabled = true;
      const spinner = document.getElementById('spinner-' + id);
      const help = document.getElementById('help-' + id);
      const success = document.getElementById('success-' + id);

      spinner?.classList.remove('hidden');

      const showError = (): void => {
        element.disabled = false;
        element.classList.add('is-invalid');
        error?.classList.remove('hidden');
      };

      const payload: OptionUpdatePayload = { key: id, value: value };

      axios
        .post<OptionUpdateResponse>(this.url_update, payload, { headers: { 'X-CSRF-TOKEN': this.csrf_token } })
        .then((response) => {
          if (response.data.success) {
            help?.classList.add('hidden');
            success?.classList.remove('hidden');
            element.classList.add('is-valid');

            setTimeout(() => {
              element.classList.remove('is-valid');
              help?.classList.remove('hidden');
              success?.classList.add('hidden');
            }, 3000);
            element.disabled = false;
          } else {
            showError();
            console.error(response.data.msg);
          }
          spinner?.classList.add('hidden');
        })
        .catch((e: unknown) => {
          spinner?.classList.add('hidden');
          showError();
          console.error(e);
        })
        .finally(() => {
          emitter.emit('reset-check-confirm');
        });
    },
  },
});
</script>

<template></template>
