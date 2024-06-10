<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Formulaire de création / édition d'une FAQ
 */
import axios from "axios";
import Toast from "../../../../Components/Global/Toast.vue";
import Modal from "../../../../Components/Global/Modal.vue";
import SchemaDatabase from "../../../../Components/DatabaseManager/SchemaDatabse.vue";
import SchemaTable from "../../../../Components/DatabaseManager/SchemaTable.vue";

export default {
  name: "DatabaseManager",
  components: {
    SchemaTable,
    SchemaDatabase,
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
      tables: Object,
      disabledListeTales: true,
      schemaTable: Object,
      show: 'schemaDatabase',
      optionData: {
        all: true,
        tables: [],
        data: 'dump',
      },
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
      this.show = 'schemaDatabase';
      this.loading = true;
      axios.get(this.urls.load_schema_database).then((response) => {
        this.result = response.data.query;
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });
    },

    /**
     * Affiche le schema de la base de donnée
     */
    showSchemaDatabase() {
      this.show = 'schemaDatabase';
    },

    /**
     * Charge le schema de la table
     * @param table
     */
    loadSchemaTable(table) {
      this.schemaTable = Object;
      this.show = 'schemaTable';
      this.loading = true;
      axios.get(this.urls.load_schema_table + '/' + table).then((response) => {
        this.schemaTable = response.data.result;
        console.log(this.schemaTable);
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });
    },

    /**
     * Charge les listes des sauvegardes faites
     */
    loadListeDump() {
      this.show = 'dumps';
    },

    /**
     * Ouverture de la modale pour la génération du dump SQL
     */
    openModalDumpSQL() {
      this.loading = true;
      axios.get(this.urls.load_tables_database).then((response) => {
        this.tables = response.data.tables;
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
        this.updateModale('modaleDumpOption', true);
      });

    },


    /**
     * Créer une nouvelle FAQ
     */
    dumpSQL() {

      this.loading = true;
      axios.post(this.urls.save_database, {
        'options': this.optionData
      }).then((response) => {

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
        this.closeModal('modaleDumpOption');
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
      <div class="btn btn-secondary me-2" @click="this.showSchemaDatabase">
        <i class="bi bi-table"></i> {{ this.translate.btn_schema_bdd }}
      </div>
      <div class="btn btn-secondary me-2" @click="this.openModalDumpSQL">
        <i class="bi bi-database-fill-down"></i> {{ this.translate.btn_generate_dump }}
      </div>
      <div class="btn btn-secondary" @click="this.loadListeDump">
        <i class="bi bi-filetype-sql"></i> {{ this.translate.btn_liste_dump }}
      </div>
      <div class="block-page">
        <SchemaDatabase v-if="this.show === 'schemaDatabase' && !this.result.length"
            :data="this.result"
            :translate="this.translate.schema_database"
            @load-schema-table="this.loadSchemaTable"
        ></SchemaDatabase>
        <div v-if="this.show === 'schemaTable'">
          <SchemaTable
              :data="this.schemaTable"
              :translate="this.translate.schema_table"
          >
          </SchemaTable>
        </div>
        <div v-if="this.show === 'dumps'">
          Liste dump
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
        <i class="bi bi-database-fill-down"></i>
        {{ this.translate.modale_dump_option.title }}
      </template>
      <template #body>
        <div class="row">
          <div class="col-6">
            <h5>{{ this.translate.modale_dump_option.sub_title_1 }}</h5>
            <div class="mb-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value=""
                    id="flexCheckDefault" v-model="this.optionData.all" @click="this.optionData.all === false ? this.disabledListeTales = true : this.disabledListeTales = false">
                <label class="form-check-label" for="flexCheckDefault">
                  {{ this.translate.modale_dump_option.select_all }}
                </label>
              </div>
            </div>
            <div class="mb-2">
              <label for="select-multi-table" class="form-label">{{ this.translate.modale_dump_option.select_tables }}</label>
              <select id="select-multi-table" class="form-select" size="15" :disabled="this.disabledListeTales" multiple v-model="this.optionData.tables">
                <option v-for="table in this.tables" :value="table.name">
                  {{ table.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="col-6">
            <h5>{{ this.translate.modale_dump_option.sub_title_2 }}</h5>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="option-dump-data" id="option-dump-data-1" value="table" v-model="this.optionData.data" checked>
              <label class="form-check-label" for="option-dump-data-1">
                {{ this.translate.modale_dump_option.option_table }}
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="option-dump-data" id="option-dump-data-2" value="data" v-model="this.optionData.data">
              <label class="form-check-label" for="option-dump-data-2">
                {{ this.translate.modale_dump_option.option_data }}
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="option-dump-data" id="option-dump-data-3" value="table_data" v-model="this.optionData.data">
              <label class="form-check-label" for="option-dump-data-3">
                {{ this.translate.modale_dump_option.option_table_data }}
              </label>
            </div>

            <div class="alert alert-secondary mt-5">
              <h6><i class="bi bi-info-circle-fill"></i> {{ this.translate.modale_dump_option.help_title }}</h6>
              <div v-html="this.translate.modale_dump_option.help_body"></div>
            </div>

          </div>
        </div>
      </template>
      <template #footer>
        <div class="btn btn-secondary" @click="this.dumpSQL">{{ this.translate.modale_dump_option.btn_generate }}</div>
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