<script>
/**
 * Permet d'afficher une modale de confirmation si un champ à été modifié sans sauvegarde
 * @author Gourdon Aymeric
 * @version 1.0
 */
import { emitter } from '../../../utils/useEvent';
import Modal from '../../Components/Global/Modal.vue';

export default {
  name: 'CheckConfirm',
  components: { Modal },
  props: {
    option: String,
    translate: Object,
  },
  emits: [],
  data() {
    return {
      isChange: false,
      excludeClass: 'no-control',
      tmpUrl: '',
      modalTab: {
        confirmModal: false,
      },
    };
  },
  mounted() {
    document.addEventListener('click', this.onClick);
    document.addEventListener('input', (evt) => {
      let tabClass = evt.target.className.split(' ');

      // Si on détecte la class excluante, on ne fait rien
      if (!tabClass.includes(this.excludeClass)) {
        this.isChange = true;
      }
    });

    emitter.on('reset-check-confirm', async () => {
      this.reset();
    });
  },
  beforeDestroy() {
    document.removeEventListener('click', this.onClick);
    document.removeEventListener('input', (evt) => {});
  },
  computed: {},
  methods: {
    reset() {
      this.isChange = false;
    },

    /**
     * Met à jour le status d'une modale défini par son id et son état
     * @param nameModale
     * @param state true|false
     */
    updateModal(nameModale, state) {
      this.modalTab[nameModale] = state;
    },

    /** Ferme la modale **/
    closeModal(id) {
      this.updateModal(id, false);
    },

    /**
     * Évènement onclick
     * @param ev
     * @returns {boolean}
     */
    onClick(ev) {
      let target = ev.target;
      let parent = target.parentElement;
      let tabClass = '';

      if (target.className instanceof SVGAnimatedString) {
        return true;
      }

      tabClass = target.className.split(' ');

      // Si on détecte la class excluante, on ne fait rien
      if (tabClass.includes(this.excludeClass)) {
        return true;
      }

      // Si un lien est détecté ou le parent direct est un lien
      if (target.href !== undefined || parent.href !== undefined) {
        if (target.href !== undefined) {
          this.tmpUrl = ev.target.href;
        } else {
          this.tmpUrl = parent.href;
        }

        if (this.isChange) {
          this.updateModal('confirmModal', true);
          ev.preventDefault();
          ev.stopImmediatePropagation();
        }
      }
    },

    /**
     * Redirige vers le lien défini
     */
    redirect() {
      this.isChange = false;
      document.location.href = this.tmpUrl;
    },
  },
};
</script>

<template>
  <modal
    :id="'confirmModal'"
    :show="this.modalTab.confirmModal"
    @close-modal="this.closeModal"
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
    <template #title>{{ this.translate.titre }}</template>
    <template #body>
      <p v-html="this.translate.corps"></p>
    </template>
    <template #footer>
      <button type="button" class="btn btn-primary btn-md me-2" @click="this.redirect">
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

        {{ this.translate.oui }}
      </button>
      <button type="button" class="btn btn-outline-dark btn-md" @click="this.updateModal('confirmModal', false)">
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

        {{ this.translate.non }}
      </button>
    </template>
  </modal>
</template>
