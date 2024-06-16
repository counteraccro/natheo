<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Options Avancées du CMS
 */
import axios from "axios";
import Toast from "../../../../Components/Global/Toast.vue";
import Modal from "../../../../Components/Global/Modal.vue";
import SchemaDatabase from "../../../../Components/DatabaseManager/SchemaDatabse.vue";
import SchemaTable from "../../../../Components/DatabaseManager/SchemaTable.vue";
import ListDump from "../../../../Components/DatabaseManager/ListDump.vue";

export default {
  name: "AdvancedOptions",
  components: {
    Modal,
    Toast
  },
  props: {
    urls: Object,
    translate: Object,
    data: Object
  },
  data() {
    return {
      loading: false,
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
    //this.loadSchemaDataBase()
  },
  methods: {

    /**
     * Chargement du schema de la base de donnée
     */
    /*loadSchemaDataBase() {
      this.show = 'schemaDatabase';
      this.loading = true;
      axios.get(this.urls.load_schema_database).then((response) => {
        this.result = response.data.query;
         if (response.data.success === true) {
          this.toasts.toastSuccess.msg = response.data.msg;
          this.toasts.toastSuccess.show = true;
        } else {
          this.toasts.toastError.msg = response.data.msg;
          this.toasts.toastError.show = true;
        }
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });
    },*/

    /**
     * Défini si on est en environnement de dev ou non
     * @returns {boolean}
     */
    isDevEnv()
    {
      console.log(this.data.app_env);
      return this.data.app_env === 'dev';
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


  <div id="block-advanced-options" :class="this.loading === true ? 'block-grid' : ''">

    <div class="card border-secondary">
      <div class="card-header text-bg-secondary">
        {{ this.translate.switch_env_title }}
      </div>
      <div class="card-body">
        <h5 class="card-title">
          <span v-if="this.isDevEnv()">{{ this.translate.switch_env_subtitle_dev }}</span>
          <span v-else>{{ this.translate.switch_env_subtitle_prod }}</span>
        </h5>
        <div v-if="this.isDevEnv()">
          <span class="card-text">{{ this.translate.switch_env_define_dev }}</span>
          <ul>
            <li>{{ this.translate.switch_env_define_dev_1 }}</li>
            <li>{{ this.translate.switch_env_define_dev_2 }}</li>
            <li>{{ this.translate.switch_env_define_dev_3 }}</li>
          </ul>
        </div>
        <p v-else class="card-text">{{ this.translate.switch_env_define_prod }}</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
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
      titre
    </template>
    <template #body>
      body
    </template>
    <template #footer>
      footer
    </template>
  </modal>
  <!-- fin modale confirmation suppression -->
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