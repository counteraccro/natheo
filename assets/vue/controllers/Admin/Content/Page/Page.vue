<script>/**
 * Permet d'ajouter ou éditer une page
 * @author Gourdon Aymeric
 * @version 1.0
 */
import axios from "axios";
import PageContentForm from "../../../../Components/Page/PageContentForm.vue";
import PageHistory from "../../../../Components/Page/PageHistory.vue";
import {Toast} from "bootstrap";

export default {
  name: 'Page',
  components: {
    PageContentForm,
    PageHistory
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
      history: []
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
        this.toast.show();
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
      });
    },

    /**
     * Recharge l'historique de la page en fonction de son id
     * @param id
     */
    reloadPageHistory(rowId)
    {
      axios.post(this.urls.reload_page_history, {
        'row_id': rowId,
        'id' : this.id
      }).then((response) => {
        this.page = response.data.page;
      }).catch((error) => {
        console.log(error);
      }).finally(() => {
      });
    }
  }
}


</script>

<template>

  <nav>
    <select class="form-select float-end w-25" @change="this.switchLocale($event)">
      <option value="" selected>{{ this.translate.select_locale }}</option>
      <option v-for="(language, key) in this.locales.localesTranslate" :value="key" :selected="key===this.currentLocale">{{ language }}</option>
    </select>
    <div class="nav nav-pills mb-3" id="nav-tab-option-system" role="tablist">
      <button class="nav-link active" @click="this.switchTab('content')" id="content-tab" data-bs-toggle="tab" data-bs-target="#nav-content" type="button" role="tab" aria-selected="true">
        <i class="bi bi-file-text"></i> {{ this.translate.onglet_content }}
      </button>
      <button class="nav-link" @click="this.switchTab('seo')" id="seo-tab" data-bs-toggle="tab" data-bs-target="#nav-seo" type="button" role="tab" aria-selected="false" tabindex="-1">
        <i class="bi bi-tools"></i> {{ this.translate.onglet_seo }}
      </button>
      <button class="nav-link" @click="this.switchTab('tags')" id="tags-tab" data-bs-toggle="tab" data-bs-target="#nav-tags" type="button" role="tab" aria-selected="false" tabindex="-1">
        <i class="bi bi-tags"></i> {{ this.translate.onglet_tags }}
      </button>
      <button class="nav-link" @click="this.switchTab('history')" id="tags-history" data-bs-toggle="tab" data-bs-target="#nav-history" type="button" role="tab" aria-selected="false" tabindex="-1">
        <i class="bi bi-clock-history"></i> {{ this.translate.onglet_history }}
      </button>
    </div>
  </nav>
  <div class="tab-content" id="myTabContent" :class="this.loading === true ? 'block-grid' : ''">
    <div class="tab-pane fade show active" id="nav-content" role="tabpanel" aria-labelledby="content-tab" tabindex="0">
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
    <div class="tab-pane fade" id="nav-tags" role="tabpanel" aria-labelledby="tags-tab" tabindex="0">Tab2</div>
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


  <div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-body text-white bg-success">
        <i class="bi bi-check-circle-fill"></i>
        {{ this.translate.msg_auto_save_success }}
      </div>
    </div>
  </div>

</template>