<script>/**
 * Permet d'ajouter ou éditer une page
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from "axios";
import PageContentForm from "../../../../Components/Page/PageContentForm.vue";
import PageHistory from "../../../../Components/Page/PageHistory.vue";
import {Toast, Tab} from "bootstrap";
import AutoComplete from "../../../../Components/Global/AutoComplete.vue";
import {emitter} from "../../../../../utils/useEvent";
import PageContent from "../../../../Components/Page/PageContent.vue";

export default {
  name: 'Page',
  components: {
    PageContent,
    AutoComplete,
    PageContentForm,
    PageHistory,
  },
  props: {
    urls: Object,
    translate: Object,
    locales: Object,
    page_datas: Object,
    id: Number
  },
  emits: [],
  data() {
    return {
      componentKey: 1,
      loading: false,
      page: [],
      currentLocale: '',
      currentTab: 'content',
      toasts: {
        autoSave: {
          toast: [],
          msg: '',
        },
        tag: {
          toast: [],
          msg: '',
          bg: 'bg-success'
        }
      },
      history: [],
      list_status: this.page_datas.list_status
    }
  },
  mounted() {
    let toastAutoSave = document.getElementById('live-toast-auto-save');
    this.toasts.autoSave.toast = Toast.getOrCreateInstance(toastAutoSave);

    let toastBootstrap = document.getElementById('live-toast-tag');
    this.toasts.tag.toast = Toast.getOrCreateInstance(toastBootstrap);

    this.currentLocale = this.locales.current;
    this.loadTabContent();
  },
  computed: {},
  methods: {

    /**
     * Change la clé du component pour forcer le rafraichissement
     */
    updateComponentKey() {
      this.componentKey++;
    },
    /**
     * Permet de changer la locale pour la création/édition d'une page
     * @param event
     */
    switchLocale(event) {
      this.currentLocale = event.target.value;
      this.loadTab();
    },

    /**
     * Permet de changer d'onglet
     * @param tab
     */
    switchTab(tab) {
      this.currentTab = tab;
      this.loadTab();
    },

    /**
     * Charge le contenu de l'onglet courant
     */
    loadTab() {
      switch (this.currentTab) {
        case "content":
          //this.loadTabContent();
          break;
        case "seo" :
          break;
        case "tags" :
          break;
        case "history" :
          this.loadTabHistory()
          break;
        default:
          console.log('Erreur tab')
      }
    },

    /**
     * Charge le contenu de l'onglet content
     */
    loadTabContent() {
      this.loading = true;
      axios.post(this.urls.load_tab_content, {
        'id': this.id,
        'locale': this.currentLocale
      }).then((response) => {
        this.page = response.data.page;
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.loading = false
      });
    },

    /**
     * Charge l'historique des modifications de la page
     */
    loadTabHistory() {
      this.loading = true;
      axios.post(this.urls.load_tab_history, {
        'id': this.id,
      }).then((response) => {
        this.history = response.data.history;
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.loading = false
      });
    },

    /**
     * Permet une sauvegarde automatique
     * @param page
     */
    autoSave(page) {

      axios.post(this.urls.auto_save, {
        'page': page
      }).then((response) => {
        this.toasts.autoSave.msg = this.translate.msg_auto_save_success;
        this.toasts.autoSave.toast.show();
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        // On lance le rechargement du render
        emitter.emit('load-render');
      });
    },

    /**
     * Ajoute un objet content ou le met à jour
     * @param content
     */
    addContent(content) {
      console.log(id);
      console.log(content);
    },

    /**
     * Force l'autoSave après la mise à jour d'un content
     */
    updateContent() {
      this.autoSave(this.page)
    },

    /**
     * Sauvegarde une page
     */
    save() {
      this.loading = true;
      axios.post(this.urls.save, {
        'page': this.page
      }).then((response) => {
        this.toasts.autoSave.msg = response.data.msg;
        this.toasts.autoSave.toast.show();
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.loading = false;
      });
    },

    /**
     * Recharge l'historique de la page en fonction de son id
     * @param rowId
     */
    reloadPageHistory(rowId) {
      this.loading = true;
      axios.post(this.urls.reload_page_history, {
        'row_id': rowId,
        'id': this.id
      }).then((response) => {

        let tabContent = document.querySelector('#nav-tab-page button[data-bs-target="#nav-content"]');
        Tab.getInstance(tabContent).show();
        this.page = response.data.page;
        this.toasts.autoSave.msg = response.data.msg;
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.toasts.autoSave.toast.show();
        this.loading = false;
        this.updateComponentKey();
      });
    },

    /*** Bloc Tag ***/

    /**
     * Ajoute un tag à la page
     * @param tag
     */
    addTag(tag) {

      axios.post(this.urls.tag_by_name, {
        'label': tag,
        'locale': this.currentLocale
      }).then((response) => {
        let tag = response.data.tag;

        if (tag !== null) {
          this.page.tags.push(tag);
          this.toasts.tag.bg = 'bg-success';
          this.toasts.tag.msg = this.translate.msg_add_tag_success;
          this.toasts.tag.toast.show();
          this.autoSave(this.page)
        } else {
          this.toasts.tag.bg = 'bg-warning';
          this.toasts.tag.msg = response.data.msg;
          this.toasts.tag.toast.show();
        }

      }).catch((error) => {
        console.log(error);
      }).finally(() => {
      });

    },

    /**
     * Génère le label en fonction de la local
     * @param tag
     */
    getTagLabel(tag) {
      let label = 'NaN';
      let locale = this.currentLocale;
      tag.tagTranslations.forEach(function (translate) {
        if (translate.locale === locale) {
          label = translate.label;
          return false;
        }
      });
      return label;
    },

    /**
     * Supprime un tag
     * @param tag
     */
    removeTag(tag) {
      console.log(tag.id);

      for (let i = 0; i < this.page.tags.length; i++) {
        if (this.page.tags[i].id === tag.id) {
          let spliced = this.page.tags.splice(i, 1);
        }
      }
      this.toasts.tag.msg = this.translate.msg_del_tag_success;
      this.toasts.tag.toast.show();

      this.autoSave(this.page);

    }

    /*** Fin bloc tag ***/

  }
}


</script>

<template>

  <div id="global-page-form">
    <nav>
      <select class="form-select float-end w-25" @change="this.switchLocale($event)">
        <option value="" selected>{{ this.translate.select_locale }}</option>
        <option v-for="(language, key) in this.locales.localesTranslate" :value="key"
            :selected="key===this.currentLocale">{{ language }}
        </option>
      </select>
      <div class="nav nav-pills mb-3" id="nav-tab-page" role="tablist">
        <button class="nav-link active" @click="this.switchTab('content')" id="content-tab" data-bs-toggle="tab"
            data-bs-target="#nav-content" type="button" role="tab" aria-selected="true">
          <i class="bi bi-file-text"></i> {{ this.translate.onglet_content }}
        </button>
        <button class="nav-link" @click="this.switchTab('seo')" id="seo-tab" data-bs-toggle="tab"
            data-bs-target="#nav-seo" type="button" role="tab" aria-selected="false" tabindex="-1">
          <i class="bi bi-tools"></i> {{ this.translate.onglet_seo }}
        </button>
        <button class="nav-link" @click="this.switchTab('tags')" id="tags-tab" data-bs-toggle="tab"
            data-bs-target="#nav-tags" type="button" role="tab" aria-selected="false" tabindex="-1">
          <i class="bi bi-tags"></i> {{ this.translate.onglet_tags }}
        </button>
        <button class="nav-link" @click="this.switchTab('history')" id="history-tab" data-bs-toggle="tab"
            data-bs-target="#nav-history" type="button" role="tab" aria-selected="false" tabindex="-1">
          <i class="bi bi-clock-history"></i> {{ this.translate.onglet_history }}
        </button>
        <button class="nav-link" @click="this.switchTab('save')" id="save-tab" data-bs-toggle="tab"
            data-bs-target="#nav-save" type="button" role="tab" aria-selected="false" tabindex="-1">
          <i class="bi bi-floppy"></i> {{ this.translate.onglet_save }}
        </button>
      </div>
    </nav>
    <div class="tab-content" id="page-tab" :class="this.loading === true ? 'block-grid' : ''">

      <!-- Formulaire page -->
      <div class="tab-pane fade show active" id="nav-content" role="tabpanel" aria-labelledby="content-tab"
          tabindex="0">
        <div v-if="this.loading" class="overlay">
          <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
            <div class="spinner-border text-primary" role="status"></div>
            <span class="txt-overlay">{{ this.translate.loading }}</span>
          </div>
        </div>
        <page-content-form :key="12 + '-' + this.componentKey"
            :locale="this.currentLocale"
            :page="this.page"
            :translate="this.translate.page_content_form"
            :list-render="this.page_datas.list_render"
            @auto-save="this.autoSave"
        />

        <div id="page-content">
          <page-content :key="13 + '-' + this.componentKey"
              :locale="this.currentLocale"
              :url="this.urls.au"
              :translate="this.translate.page_content"
              :page="this.page"
              @auto-save="this.autoSave"
          />
        </div>

      </div>
      <!-- Fin Formulaire page -->
      <!-- Bloc SEO -->
      <div class="tab-pane fade" id="nav-seo" role="tabpanel" aria-labelledby="seo-tab" tabindex="0">
        Tab1
      </div>
      <!-- Fin bloc SEO -->
      <!-- Bloc tag -->
      <div class="tab-pane fade" id="nav-tags" role="tabpanel" aria-labelledby="tags-tab" tabindex="0">

        <h5>{{ this.translate.tag_title }}</h5>

        <auto-complete
            :locale="this.currentLocale"
            :url="this.urls.auto_complete_tag"
            :translate="this.translate.auto_complete"
            @select-value="this.addTag"
        />

        <h6>{{ this.translate.tag_sub_title }}</h6>
        <div id="block-tag">
          <span v-for="tag in this.page.tags" class="me-1 badge rounded-pill badge-nat" :style="'background-color:' +tag.color">
           {{ this.getTagLabel(tag) }}
            <i class="bi bi-x-circle" style="cursor: pointer" @click="this.removeTag(tag)"></i>
          </span>
        </div>

      </div>
      <!-- fin bloc tag -->
      <!-- Bloc history -->
      <div class="tab-pane fade" id="nav-history" role="tabpanel" aria-labelledby="history-tab" tabindex="0">
        <div v-if="this.loading" class="overlay">
          <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
            <div class="spinner-border text-primary" role="status"></div>
            <span class="txt-overlay">{{ this.translate.loading }}</span>
          </div>
        </div>
        <page-history
            :translate="this.translate.page_history"
            :history="this.history"
            @reload-page-history="this.reloadPageHistory"
        />

      </div>
      <!-- fin bloc history -->
      <!-- Bloc save -->
      <div class="tab-pane fade" id="nav-save" role="tabpanel" aria-labelledby="seo-tab" tabindex="0">

        <div v-if="this.loading" class="overlay">
          <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
            <div class="spinner-border text-primary" role="status"></div>
            <span class="txt-overlay">{{ this.translate.loading }}</span>
          </div>
        </div>

        <h5>{{ this.translate.page_save.title }}</h5>

        <div class="mb3">
          <label for="list-status-page" class="form-label">{{ this.translate.page_save.list_status_label }}</label>
          <select id="list-status-page" class="form-select" aria-label="Default select example" v-model="this.page.status">
            <option v-for="(value, key) in this.list_status" :value="parseInt(key)">{{ value }}</option>
          </select>
          <div id="list-status-help" class="form-text">{{ this.translate.page_save.list_status_help }}</div>
        </div>

        <div class="mt-3">
          <div class="btn btn-secondary me-1" @click="this.save">
            <i class="bi bi-floppy"></i> {{ this.translate.page_save.btn_save }}
          </div>
          <div class="btn btn-secondary">
            <i class="bi bi-box-arrow-up-right"></i> {{ this.translate.page_save.btn_see_ext }}
          </div>
        </div>

      </div>
      <!-- fin bloc save -->
    </div>
  </div>


  <div class="toast-container position-fixed top-0 end-0 p-2">
    <div id="live-toast-auto-save" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-body text-white bg-success">
        <i class="bi bi-check-circle-fill"></i>
        {{ this.toasts.autoSave.msg }}
      </div>
    </div>

    <div id="live-toast-tag" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-body text-white" :class="this.toasts.tag.bg">
        <i class="bi bi-check-circle-fill"></i>
        {{ this.toasts.tag.msg }}
      </div>
    </div>
  </div>

</template>