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

export default {
  name: 'Page',
  components: {
    AutoComplete,
    PageContentForm,
    PageHistory,
  },
  props: {
    urls: Object,
    translate: Object,
    locales: Object,
    id: Number
  },
  emits: [],
  data() {
    return {
      loading: false,
      page: [],
      currentLocale: '',
      currentTab: 'content',
      toast: [],
      history: [],
      msgToast: '',
    }
  },
  mounted() {

    let tBootstrap = document.getElementById('liveToast');
    this.toast = Toast.getOrCreateInstance(tBootstrap);

    this.currentLocale = this.locales.current;
    this.loadTabContent();
  },
  computed: {},
  methods: {
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
        this.msgToast = this.translate.msg_auto_save_success;
        this.toast.show();
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
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
        console.log(tabContent);
        Tab.getInstance(tabContent).show();
        this.page = response.data.page;
        this.msgToast = response.data.msg;
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
        this.toast.show();
        this.loading = false;
      });
    },

    /*** Bloc Tag ***/

    /**
     * Ajoute un tag à la page
     * @param tag
     */
    addTag(tag)
    {

      axios.post(this.urls.tag_by_name, {
        'label': tag,
        'locale' : this.currentLocale
      }).then((response) => {
        let tag = response.data.tag;
        this.page.tags.push(tag);

        this.msgToast = this.translate.msg_add_tag_success;
        this.toast.show();
        this.autoSave(this.page)

      }).catch((error) => {
        console.log(error);
      }).finally(() => {
      });

    },

    /**
     * Génère le label en fonction de la local
     * @param tag
     */
    getTagLabel(tag)
    {
      let label = 'NaN';
      let locale = this.currentLocale;
      tag.tagTranslations.forEach(function (translate) {
        if(translate.locale === locale)
        {
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
    removeTag(tag)
    {
      console.log(tag.id);

      for (let i = 0; i < this.page.tags.length; i++) {
        if (this.page.tags[i].id === tag.id) {
          let spliced = this.page.tags.splice(i, 1);
        }
      }
      this.msgToast = this.translate.msg_del_tag_success;
      this.toast.show();

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
        <button class="nav-link" @click="this.switchTab('history')" id="tags-history" data-bs-toggle="tab"
                data-bs-target="#nav-history" type="button" role="tab" aria-selected="false" tabindex="-1">
          <i class="bi bi-clock-history"></i> {{ this.translate.onglet_history }}
        </button>
      </div>
    </nav>
    <div class="tab-content" id="page-tab" :class="this.loading === true ? 'block-grid' : ''">
      <div class="tab-pane fade show active" id="nav-content" role="tabpanel" aria-labelledby="content-tab"
           tabindex="0">
        <div v-if="this.loading" class="overlay">
          <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
            <div class="spinner-border text-primary" role="status"></div>
            <span class="txt-overlay">{{ this.translate.loading }}</span>
          </div>
        </div>
        <page-content-form
            :locale="this.currentLocale"
            :page="this.page"
            :translate="this.translate.page_content_form"
            @auto-save="this.autoSave"
        />

        <div v-for="pageContent in this.page.pageContents">
          <div v-for="pCT in pageContent.pageContentTranslations">
            {{ pCT.text }} <br/>
            <hr/>
          </div>
        </div>

      </div>
      <div class="tab-pane fade" id="nav-seo" role="tabpanel" aria-labelledby="seo-tab" tabindex="0">Tab1</div>
      <div class="tab-pane fade" id="nav-tags" role="tabpanel" aria-labelledby="tags-tab" tabindex="0">

        <h5>{{this.translate.tag_title}}</h5>

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
    </div>
  </div>


  <div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-body text-white bg-success">
        <i class="bi bi-check-circle-fill"></i>
        {{ this.msgToast }}
      </div>
    </div>
  </div>

</template>