<script>/**
 * Permet d'ajouter ou éditer un menu
 * @author Gourdon Aymeric
 * @version 1.O
 */
import axios from "axios";
import Toast from "../../../../Components/Global/Toast.vue";
import Modal from "../../../../Components/Global/Modal.vue";

export default {
  name: 'Menu',
  components: {
    Toast, Modal
  },
  props: {
    urls: Object,
    translate: Object,
    locales: Object,
    menu_datas: Object,
    id: Number
  },
  emits: [],
  data() {
    return {
      loading: false,
      menu: [],
      currentLocale: '',
      currentPosition: '',
      listTypeByPosition: [],
      toasts: {
        toastSuccess: {
          show: false,
          msg: '',
        },
        toastError: {
          show: false,
          msg: '',
        },
        toastAutoSave: {
          show: false,
          msg: '',
        }
      },
    }
  },
  mounted() {
    this.currentLocale = this.locales.current;
    this.loadMenu();
  },
  computed: {},
  methods: {


    /**
     * Permet de changer la locale pour la création/édition d'une page
     * @param event
     */
    switchLocale(event) {
      this.currentLocale = event.target.value;
    },


    /**
     * Permet de changer de position
     */
    switchPosition() {
      this.selectListTypeByPosition(this.menu.position);
    },

    /**
     * Selectionne la liste de type en fonction en fonction de la position
     * @param position
     */
    selectListTypeByPosition(position) {
      this.listTypeByPosition = [];

      for (let key in this.menu_datas.list_type) {

        if (!this.menu_datas.list_position.hasOwnProperty(key)) continue;

        if (key === position) {
          this.listTypeByPosition = this.menu_datas.list_type[key];
          break;
        }
      }
    },


    /**
     * Charge le menu
     */
    loadMenu() {
      let url = this.urls.load_menu + '/' + this.id;
      if (this.id === null) {
        url = this.urls.load_menu;
      }
      this.loading = true;
      axios.get(url, {}
      ).then((response) => {
        this.menu = response.data.menu;
        this.selectListTypeByPosition(this.menu.position);
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false
      });
    },


    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast) {
      this.toasts[nameToast].show = false
    },
  }
}


</script>

<template>

  <div id="global-menu" :class="this.loading === true ? 'block-grid' : ''">

    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>


    <div class="row">
      <div class="col-3">
        <select id="select-language" class="form-select w-auto" v-model="this.menu.position" @change="this.switchPosition($event)">
          <option value="" selected>{{ this.translate.select_position }}</option>
          <option v-for="(position, key) in this.menu_datas.list_position" :value="key">{{ position }}
          </option>
        </select>
      </div>
      <div class="col-3">
        <select id="select-language" class="form-select w-auto" v-model="this.menu.type" :disabled="this.listTypeByPosition.length === 0">
          <option value="" selected v-if="this.listTypeByPosition.length === 0">
            {{ this.translate.select_type }}
          </option>
          <option v-for="(position, key) in this.listTypeByPosition" :value="key">{{ position }}
          </option>
        </select>
      </div>
      <div class="col-6">
        <select id="select-language" class="form-select w-auto float-end" @change="this.switchLocale($event)">
          <option value="" selected>{{ this.translate.select_locale }}</option>
          <option v-for="(language, key) in this.locales.localesTranslate" :value="key"
              :selected="key===this.currentLocale">{{ language }}
          </option>
        </select>
      </div>
    </div>


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