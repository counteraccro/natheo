<script>/**
 * Permet de générer un rendu pour les pages
 * @author Gourdon Aymeric
 * @version 1.0
 */
import {emitter} from "../../../utils/useEvent";
import Modal from "../../Components/Global/Modal.vue";


export default {
  name: 'CheckConfirm',
  components: {Modal},
  props: {
    option: String,
  },
  emits: [],
  data() {
    return {
      isChange: false,
      tmpUrl: '',
      modalTab: {
        confirmModal: false,
      },
    }
  },
  mounted() {

    document.addEventListener('click', this.onClick);
    document.addEventListener("input", (evt) => {
      console.log('change');
      this.isChange = true;
    });

    emitter.on('reset-check-confirm', async () => {
      this.reset();
    });
  },
  beforeDestroy() {
    document.removeEventListener('click', this.onClick);
    document.removeEventListener("input", (evt) => {
    });
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

    closeModal(id) {
      this.updateModal(id, false)
    },

    onClick(ev) {

      let target = ev.target;
      let parent = target.parentElement

      
      console.log(target.className);

      if(target.href !== undefined || parent.href !== undefined)
      {
        if(target.href !== undefined)
        {
          this.tmpUrl = ev.target.href;
        }else {
          this.tmpUrl = parent.href;
        }
        if(this.isChange)
        {
          console.log('event change');
          this.updateModal('confirmModal', true)
          ev.preventDefault();
          ev.stopImmediatePropagation();

        }
      }

    },

    redirect()
    {
      this.isChange = false;
      document.location.href = this.tmpUrl;
    }

  }
}
</script>

<template>

  <modal
      :id="'confirmModal'"
      :show="this.modalTab.confirmModal"
      @close-modal="this.closeModal"
      :option-modal-size="'modal-lg'"
      :option-modal-backdrop="'static'"
      :option-show-close-btn="false"
  >
    <template #title><i class="bi bi-plus-square"></i> Tr alerte</template>
    <template #body>
      tr mesg
    </template>
    <template #footer>
      <button type="button" class="btn btn-primary" @click="this.redirect">
        <i class="bi bi-check2-circle"></i> Oui
      </button>
      <button type="button" class="btn btn-secondary" @click="this.updateModal('confirmModal', false)">
        <i class="bi bi-x-circle"></i> Non
      </button>
    </template>
  </modal>

</template>