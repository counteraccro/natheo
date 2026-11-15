<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.1
 * Permet de générer les events pour la sauvegarde des options systèmes et options users,
 * et le changement de langue de saisie des onglets multilingues
 */

import { defineComponent } from 'vue';
import axios from 'axios';
import { withCsrf } from '@/vue/Composables/Admin/csrf';
import { emitter } from '@/vue/Composables/Global/useEvent';
import type {
  OptionField,
  OptionTranslatableField,
  OptionUpdatePayload,
  OptionUpdateResponse,
} from '@/ts/Option/Option.type';

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
    for (const panel of this.getMultilingualPanels()) {
      panel.addEventListener('click', this.onLocaleClick);
      panel.addEventListener('input', this.onTranslatableInput);
      this.refreshTranslations(panel);
    }
  },
  beforeUnmount() {
    const elements = document.getElementsByClassName('event-input');
    for (const element of Array.from(elements)) {
      element.removeEventListener('change', this.onChange);
    }
    for (const panel of this.getMultilingualPanels()) {
      panel.removeEventListener('click', this.onLocaleClick);
      panel.removeEventListener('input', this.onTranslatableInput);
    }
  },
  methods: {
    getMultilingualPanels(): HTMLElement[] {
      return Array.from(document.querySelectorAll<HTMLElement>('[data-option-multilingual]'));
    },

    /**
     * Clic sur un bouton du sélecteur : affiche les champs de cette langue dans tout l'onglet
     * @param event
     */
    onLocaleClick(event: Event): void {
      const button = (event.target as HTMLElement).closest<HTMLButtonElement>('[data-option-locale-button]');
      const panel = event.currentTarget as HTMLElement;
      if (!button) {
        return;
      }
      const locale = button.dataset.optionLocaleButton;

      for (const block of Array.from(panel.querySelectorAll<HTMLElement>('[data-option-locale]'))) {
        block.classList.toggle('hidden', block.dataset.optionLocale !== locale);
      }
      for (const other of Array.from(panel.querySelectorAll<HTMLButtonElement>('[data-option-locale-button]'))) {
        const selected = other === button;
        other.setAttribute('aria-pressed', selected ? 'true' : 'false');
        other.classList.toggle('btn-primary', selected);
        other.classList.toggle('btn-outline-dark', !selected);
      }
    },

    onTranslatableInput(event: Event): void {
      if ((event.target as HTMLElement).dataset.locale) {
        this.refreshTranslations(event.currentTarget as HTMLElement);
      }
    },

    /**
     * Met à jour, d'après les valeurs saisies, le nombre de champs vides par langue, et sous chaque champ vide
     * le message et le placeholder du texte qui sera affiché à sa place
     * @param panel onglet multilingue
     */
    refreshTranslations(panel: HTMLElement): void {
      const defaultLocale = panel.dataset.defaultLocale ?? '';
      const fields = Array.from(panel.querySelectorAll<OptionTranslatableField>('[data-key][data-locale]'));
      const counts: Record<string, number> = {};

      for (const field of fields) {
        const locale = field.dataset.locale ?? '';
        const siblings = fields.filter((other) => other.dataset.key === field.dataset.key && other !== field);
        const defaultValue = siblings.find((other) => other.dataset.locale === defaultLocale)?.value ?? '';
        const otherValue = siblings.find((other) => other.value !== '')?.value ?? '';

        let message = '';
        let fallback = '';
        if (field.value === '') {
          counts[locale] = (counts[locale] ?? 0) + 1;
          if (locale !== defaultLocale && defaultValue !== '') {
            message = panel.dataset.msgFallbackDefault ?? '';
            fallback = defaultValue;
          } else if (otherValue !== '') {
            message = panel.dataset.msgFallbackOther ?? '';
            fallback = otherValue;
          } else {
            message = (field.dataset.fallbackLabel ? panel.dataset.msgEmptyTheme : panel.dataset.msgEmptyNothing) ?? '';
          }
        }

        field.placeholder = fallback || field.dataset.placeholder || '';
        const messageElement = document.getElementById('fallback-' + field.id);
        if (messageElement) {
          messageElement.textContent = message;
          messageElement.classList.toggle('hidden', message === '');
        }
      }

      for (const badge of Array.from(panel.querySelectorAll<HTMLElement>('[data-option-locale-count]'))) {
        const count = counts[badge.dataset.optionLocaleCount ?? ''] ?? 0;
        badge.textContent = String(count);
        badge.classList.toggle('hidden', count === 0);
        badge.setAttribute('aria-hidden', 'true');
        const label = badge.nextElementSibling as HTMLElement | null;
        if (label) {
          label.textContent = count === 0 ? '' : (label.dataset.label ?? '') + ' ' + count;
        }
      }
    },

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

      // Champ d'une langue d'une option traduisible : la clé et la langue sont portées par data-*
      const payload: OptionUpdatePayload = { key: element.dataset.key ?? id, value: value };
      if (element.dataset.locale) {
        payload.locale = element.dataset.locale;
      }

      axios
        .post<OptionUpdateResponse>(this.url_update, payload, withCsrf(this.csrf_token))
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
