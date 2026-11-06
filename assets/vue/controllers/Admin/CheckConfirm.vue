<script lang="ts">
/**
 * Permet d'afficher une modale de confirmation si un champ à été modifié sans sauvegarde
 * @author Gourdon Aymeric
 * @version 2.0
 */
import { defineComponent, type PropType } from 'vue';
import { emitter } from '@/vue/Composables/Global/useEvent';
import Modal from '@/vue/Components/Global/Modal.vue';
import type { CheckConfirmTranslate } from '@/ts/Global/CheckConfirm.type';

const EXCLUDE_CLASS = 'no-control';

export default defineComponent({
  name: 'CheckConfirm',
  components: { Modal },
  props: {
    // Valeur de l'option système OS_CONFIRM_LEAVE_FORM ('1' = active)
    option: { type: String, default: '1' },
    translate: { type: Object as PropType<CheckConfirmTranslate>, required: true },
  },
  data() {
    return {
      isChange: false,
      tmpUrl: '',
      confirmModal: false,
    };
  },
  mounted() {
    if (this.option !== '1') {
      return;
    }
    document.addEventListener('click', this.onClick);
    document.addEventListener('input', this.onInput);
    emitter.on('reset-check-confirm', this.reset);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.onClick);
    document.removeEventListener('input', this.onInput);
    emitter.off('reset-check-confirm', this.reset);
  },
  methods: {
    reset(): void {
      this.isChange = false;
    },

    /**
     * Retourne true si l'élément porte la classe excluante (ou une classe SVG, non comparable)
     * @param element
     */
    isExcluded(element: Element): boolean {
      return typeof element.className !== 'string' || element.classList.contains(EXCLUDE_CLASS);
    },

    /**
     * Évènement oninput : un champ a été modifié
     * @param ev
     */
    onInput(ev: Event): void {
      if (ev.target instanceof Element && !this.isExcluded(ev.target)) {
        this.isChange = true;
      }
    },

    /**
     * Évènement onclick : intercepte les liens si un champ a été modifié sans sauvegarde
     * @param ev
     */
    onClick(ev: MouseEvent): void {
      const target = ev.target;
      if (!(target instanceof Element) || this.isExcluded(target)) {
        return;
      }

      const link = target instanceof HTMLAnchorElement ? target : target.parentElement;
      if (!(link instanceof HTMLAnchorElement) || link.href === '') {
        return;
      }

      this.tmpUrl = link.href;
      if (this.isChange) {
        this.confirmModal = true;
        ev.preventDefault();
        ev.stopImmediatePropagation();
      }
    },

    /**
     * Redirige vers le lien défini
     */
    redirect(): void {
      this.isChange = false;
      document.location.href = this.tmpUrl;
    },
  },
});
</script>

<template>
  <modal
    :id="'confirmModal'"
    :show="confirmModal"
    @close-modal="confirmModal = false"
    :option-modal-backdrop="'static'"
    :option-show-close-btn="false"
  >
    <template #icon>
      <svg
        class="w-7 h-7 me-3"
        aria-hidden="true"
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        fill="none"
        viewBox="0 0 24 24"
      >
        <path
          stroke="currentColor"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
        />
      </svg>
    </template>
    <template #title>{{ translate.titre }}</template>
    <template #body>
      <p v-html="translate.corps"></p>
    </template>
    <template #footer>
      <button type="button" class="btn btn-outline-dark btn-sm" @click="confirmModal = false">
        <svg
          class="icon"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          fill="none"
          viewBox="0 0 24 24"
        >
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
          />
        </svg>

        {{ translate.non }}
      </button>
      <button type="button" class="btn btn-primary btn-sm" @click="redirect">
        <svg
          class="icon"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          fill="none"
          viewBox="0 0 24 24"
        >
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
          />
        </svg>

        {{ translate.oui }}
      </button>
    </template>
  </modal>
</template>
