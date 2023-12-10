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
import {toInteger} from "lodash-es";

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
          bg: 'bg-success'
        },
        tag: {
          toast: [],
          msg: '',
          bg: 'bg-success'
        },
        contentRemove: {
          toast: [],
          msg: '',
          bg: 'bg-success'
        },
        contentAdd: {
          toast: [],
          msg: '',
          bg: 'bg-success'
        }
      },
      history: [],
      historyInfo: [],
      tabErrorTemplate: {
        locale: '',
        error: false,
      },
      tabError: {
        contentForm: {
          url: {
            locales: {
              fr: false,
              en: false,
              es: false
            },
            msg: this.translate.msg_error_url_no_unique
          }
        },
        globale: {
          content: false,
        }
      },
      list_status: this.page_datas.list_status
    }
  },
  mounted() {
    let toastAutoSave = document.getElementById('live-toast-auto-save');
    this.toasts.autoSave.toast = Toast.getOrCreateInstance(toastAutoSave);

    let toastTag = document.getElementById('live-toast-tag');
    this.toasts.tag.toast = Toast.getOrCreateInstance(toastTag);

    let toastContentRemove = document.getElementById('live-toast-content-remove');
    this.toasts.contentRemove.toast = Toast.getOrCreateInstance(toastContentRemove);

    let toastContentAdd = document.getElementById('live-toast-content-add');
    this.toasts.contentAdd.toast = Toast.getOrCreateInstance(toastContentAdd);

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
        this.historyInfo = response.data.history
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
     * Affichage d'un warning si erreur
     * @param tab
     * @returns {string}
     */
    showTabError(tab) {

      let str = '';
      if (this.tabError.globale[tab]) {

        str = '<span class="text-warning">';

        switch (tab) {
          case "content":
            str += '<i class="bi bi-exclamation-triangle"></i> ';
            break;
          case "seo" :
            break;
          case "tags" :
            break;
          case "history" :
            break;
          default:
            str = '';
        }
        str += '</span> ';
      }
      return str;
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
     * Supprime un page content en fonction de son id
     * @param id
     */
    removeContent(id) {
      this.page.pageContents = this.page.pageContents.filter((content) => content.renderBlock !== id);
      this.toasts.contentRemove.msg = this.translate.msg_remove_content_success;
      this.toasts.contentRemove.toast.show();
      this.autoSave(this.page);
    },

    /**
     * Met à jour un contenu de type texte
     */
    updateContentText(id, value) {

      // TODO provoque par moment un warning Maximum recursive updates, peut être lié à l'affichage (pageContentBlock)

      // On utilise renderBlock + langue pour identifiant le bon pageContentTranslation
      let tmpId = id.split('-');

      // Passage par un tableau temporaire pour éviter les warnings de récursivités vueJS
      // Problème de référence
      let tmp = JSON.parse(JSON.stringify(this.page.pageContents));
      tmp.forEach((pC) => {
            if (pC.typeId === null) {
              pC.pageContentTranslations.forEach((pCT) => {
                if (pC.renderBlock === toInteger(tmpId[0]) && pCT.locale === tmpId[1]) {
                  pCT.text = value;
                }
              })
            }
          }
      );
      this.page.pageContents = tmp;
      this.autoSave(this.page)
    },

    /**
     * Change le renderBlock en plus ou moins
     */
    moveContent(signe, renderBlockId) {

      // TODO provoque par moment un warning Maximum recursive updates, peut être lié à l'affichage (pageContentBlock)

      let renderBlockIdReplace = 0;
      if (signe === "+") {
        renderBlockIdReplace = renderBlockId + 1;
      } else if (signe === "-") {
        renderBlockIdReplace = renderBlockId - 1;
      } else {
        return false;
      }

      let pCMove = null;
      let pCForceMove = null;

      // Passage par un tableau temporaire pour éviter les warnings de récursivités vueJS
      // Problème de référence
      let tmp = JSON.parse(JSON.stringify(this.page.pageContents));
      let tmp2 = [...tmp];
      tmp.forEach((pC) => {
            if (pC.renderBlock === renderBlockId) {
              pCMove = pC;
              tmp2 = tmp2.filter(item => item.renderBlock !== renderBlockId);
            }

            if (pC.renderBlock === renderBlockIdReplace) {
              pCForceMove = pC;
              tmp2 = tmp2.filter(item => item.renderBlock !== renderBlockIdReplace);
            }
          }
      );

      pCMove.renderBlock = renderBlockIdReplace;
      tmp2.push(pCMove);
      if (pCForceMove !== null) {
        pCForceMove.renderBlock = renderBlockId;
        tmp2.push(pCForceMove);
      }
      tmp2.sort((a, b) => (a.renderBlock > b.renderBlock ? 1 : -1));
      this.page.pageContents = tmp2;
      this.autoSave(this.page)
    },

    /**
     * Ajoute un nouveau contenu
     * @param type
     * @param type_id
     * @param renderBlockId
     */
    newContent(type, type_id, renderBlockId) {

      // TODO provoque par moment un warning Maximum recursive updates, peut être lié à l'affichage (pageContentBlock)

      axios.post(this.urls.new_content, {
        'type': type,
        'type_id': type_id,
        'renderBlock': renderBlockId
      }).then((response) => {

        // Manipulation manuelle pour éviter les warning récursif
        let pCtmp = this.page.pageContents;
        let newPcTmp = [];
        pCtmp.forEach(function (value) {
          newPcTmp.push({...value});
        })

        newPcTmp.push(response.data.pageContent);
        newPcTmp.sort((a, b) => (a.renderBlock > b.renderBlock ? 1 : -1));
        this.page.pageContents = newPcTmp;

        this.toasts.contentAdd.msg = this.translate.msg_add_content_success;
        this.toasts.contentAdd.toast.show();
        this.autoSave(this.page)
      }).catch((error) => {
        console.log('erreur');
        console.log(error);
      }).finally(() => {
      });

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

        // Cas première page, on force la redirection pour passer en mode édition
        if (response.data.redirect === true) {
          window.location.replace(response.data.url_redirect);
        }

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

    /**
     * Vérifie si l'url est unique ou non
     * @param url
     * @param id
     * @param locale
     */
    isUniqueUrl(url, id, locale) {
      axios.post(this.urls.is_unique_url_page, {
        'id': id,
        'url': url
      }).then((response) => {
        if (response.data.is_unique) {

          this.tabError.contentForm.url.locales[locale] = true;
          this.tabError.globale.content = true;

        } else {
          let tab = {};
          let isError = false;
          // Avant d'être sur qu'il n'y à pas de doublons on check ce qu'a saisi l'utilisateur
          this.page.pageTranslations.forEach(function (data) {
            if (data.url === url && data.locale !== locale) {
              isError = true;
            }
          });

          if (isError) {
            this.tabError.contentForm.url.locales[locale] = true;
            this.tabError.globale.content = true;
          }


          // Pas d'erreur
          if (!isError) {

            console.log(locale);
            console.log(this.tabError.contentForm.url.locales)

            this.tabError.contentForm.url.locales[locale] = false;

            let check = this.tabError.contentForm.url.locales;
            if (!check.fr && !check.en && !check.es) {
              this.tabError.globale.content = false;
            }
            this.autoSave(this.page)
          }
        }
      }).catch((error) => {
        console.log(error);
      }).finally(() => {

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

    <div v-if="historyInfo.show_msg" class="alert alert-primary alert-dismissible">
      <h5 class="alert-heading"><i class="bi bi-info-circle"></i> {{ this.translate.msg_titre_restore_history }}</h5>
      <p>{{ historyInfo.msg }}</p>

      <div class="btn btn-sm btn-secondary" data-bs-dismiss="alert" @click="this.reloadPageHistory(historyInfo.id)">
        <i class="bi bi-arrow-clockwise"></i>
        {{ this.translate.msg_btn_restore_history }}
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <nav>
      <select id="select-language" class="form-select float-end w-25" @change="this.switchLocale($event)">
        <option value="" selected>{{ this.translate.select_locale }}</option>
        <option v-for="(language, key) in this.locales.localesTranslate" :value="key"
            :selected="key===this.currentLocale">{{ language }}
        </option>
      </select>
      <div class="nav nav-pills mb-3" id="nav-tab-page" role="tablist">
        <button class="nav-link active" @click="this.switchTab('content')" id="content-tab" data-bs-toggle="tab"
            data-bs-target="#nav-content" type="button" role="tab" aria-selected="true">
          <span v-html="this.showTabError('content')"></span>
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
            :tab-error="this.tabError.contentForm"
            @auto-save="this.autoSave"
            @is-unique-url="this.isUniqueUrl"
        />

        <div id="page-content">
          <page-content :key="13 + '-' + this.componentKey"
              :locale="this.currentLocale"
              :url="this.urls.liste_content_by_id"
              :list-content="this.page_datas.list_content"
              :translate="this.translate.page_content"
              :page="this.page"
              @update-content-text="this.updateContentText"
              @remove-content="this.removeContent"
              @new-content="this.newContent"
              @move-content="this.moveContent"
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


  <!-- toast -->
  <div class="toast-container position-fixed top-0 end-0 p-2">
    <div id="live-toast-auto-save" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-body text-white" :class="this.toasts.autoSave.bg">
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

    <div id="live-toast-content-remove" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-body text-white" :class="this.toasts.contentRemove.bg">
        <i class="bi bi-check-circle-fill"></i>
        {{ this.toasts.contentRemove.msg }}
      </div>
    </div>

    <div id="live-toast-content-add" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-body text-white" :class="this.toasts.contentAdd.bg">
        <i class="bi bi-check-circle-fill"></i>
        {{ this.toasts.contentAdd.msg }}
      </div>
    </div>

  </div>


  <!-- fin toast -->

</template>