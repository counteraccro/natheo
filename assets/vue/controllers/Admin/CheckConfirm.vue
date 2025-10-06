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

      let tabClass = target.className.split(' ');

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
    <template #title><i class="bi bi-exclamation-triangle"></i> {{ this.translate.titre }}</template>
    <template #body>
      {{ this.translate.corps }}
    </template>
    <template #footer>
      <button type="button" class="btn btn-primary" @click="this.redirect">
        <i class="bi bi-check2-circle"></i> {{ this.translate.oui }}
      </button>
      <button type="button" class="btn btn-secondary" @click="this.updateModal('confirmModal', false)">
        <i class="bi bi-x-circle"></i> {{ this.translate.non }}
      </button>
    </template>
  </modal>
</template>
