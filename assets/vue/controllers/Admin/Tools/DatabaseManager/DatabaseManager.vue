<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Formulaire de création / édition d'une FAQ
 */
import axios from "axios";
import Toast from "../../../../Components/Global/Toast.vue";
import Modal from "../../../../Components/Global/Modal.vue";

export default {
  name: "DatabaseManager",
  components: {
    Modal,
    Toast
  },
  props: {
    urls: Object,
    translate: Object,
  },
  data() {
    return {
      loading: false,
      result: Object,
      modalTab: {
        modaleDumpOption: false,
      },
      toasts: {
        toastSuccess: {
          show: false,
          msg: '',
        },
        toastError: {
          show: false,
          msg: '',
        }
      },
    }
  },
  mounted() {
    this.loadSchemaDataBase()
  },
  methods: {

    /**
     * Chargement du schema de la base de donnée
     */
    loadSchemaDataBase() {
      this.loading = true;
      axios.get(this.urls.load_schema_database).then((response) => {
        this.result = response.data.query;
        console.log(this.result.stat.nbElement);
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });
    },


    /**
     * Créer une nouvelle FAQ
     */
    dumpSQL() {

      this.loading = true;
      axios.get(this.urls.save_database).then((response) => {

        if (response.data.success === true) {
          this.toasts.toastSuccess.msg = response.data.msg;
          this.toasts.toastSuccess.show = true;
        } else {
          this.toasts.toastError.msg = response.data.msg;
          this.toasts.toastError.show = true;
          this.loading = false;
        }
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
      });
    },


    /**
     * Ferme un toast en fonction de son id
     * @param nameToast
     */
    closeToast(nameToast) {
      this.toasts[nameToast].show = false
    },

    /**
     * Met à jour le status d'une modale défini par son id et son état
     * @param nameModale
     * @param state true|false
     */
    updateModale(nameModale, state) {
      this.modalTab[nameModale] = state;
    },

    /**
     * Ferme une modale
     * @param nameModale
     */
    closeModal(nameModale) {
      this.updateModale(nameModale, false);
    },
  },
}
</script>

<template>


  <div id="block-sql-manager" :class="this.loading === true ? 'block-grid' : ''">

    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>

    <div>
      <div class="btn btn-secondary" @click="this.updateModale('modaleDumpOption', true);">Dump</div>
      <div class="btn btn-secondary" @click="this.dumpSQL">Rafraichir</div>
      <div class="btn btn-secondary" @click="this.dumpSQL">Suavegardes</div>
      <div class="block-page">
        <div class="table-responsive">
          <table class="table table-sm table-striped table-hover" aria-describedby="table">
            <thead>
            <tr>
              <th v-for="(header, key) in this.result.header">
                {{ header }}
              </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="row in this.result.result">
              <td v-for="(header, key) in this.result.header">
                {{ row[key] }}
              </td>
            </tr>
            </tbody>
            <tfoot>
            <tr class="table-secondary" v-if="!this.result.length">
              <th>{{ this.translate.nb_row_total }} :</th>
              <th>{{ this.result.stat.nbTable }}</th>
              <th>{{ this.result.stat.nbElement }}</th>
              <th>{{ this.result.stat.sizeBite }}</th>
            </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- modale confirmation suppression -->
    <modal
        :id="'modaleDumpOption'"
        :show="this.modalTab.modaleDumpOption"
        @close-modal="this.closeModal"
        :optionModalSize="'modal-lg'"
        :option-show-close-btn="false">
      <template #title>
        <i class="bi bi-sign-stop-fill"></i>
        {{ this.translate.modale_dump_option.title }}
      </template>
      <template #body>
        option
      </template>
      <template #footer>
        footer
      </template>
    </modal>
    <!-- fin modale nouvelle categogie -->

  </div>

  <!-- toast -->
  <div class="toast-container position-fixed top-0 end-0 p-2">

    <toast
        :id="'toastSuccess'"
        :option-class-header="'text-success'"
        :show="this.toasts.toastSuccess.show"
        @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-check-circle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast_title_success }}</strong>
        <small class="text-black-50">{{ this.translate.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastSuccess.msg"></div>
      </template>
    </toast>

    <toast
        :id="'toastError'"
        :option-class-header="'text-danger'"
        :show="this.toasts.toastError.show"
        @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-exclamation-triangle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast_title_error }}</strong>
        <small class="text-black-50">{{ this.translate.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastError.msg"></div>
      </template>
    </toast>

  </div>

</template>