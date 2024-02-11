<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Formulaire de création / édition d'une FAQ
 */
import axios from "axios";
import FieldEditor from "../../../../Components/Global/FieldEditor.vue";
import MarkdownEditor from "../../../../Components/Global/MarkdownEditor.vue";
import {Toast, Tab} from "bootstrap";

export default {
  name: "Faq",
  components: {
    MarkdownEditor,
    FieldEditor
  },
  props: {
    urls: Object,
    translate: Object,
    locales: Object,
    id: Number
  },
  data() {
    return {
      faq: Object,
      currentLocale: this.locales.current,
      loading: false,
      loadData: false,
      keyVal: 1,
      title: '',
      toasts: {
        success: {
          toast: [],
          msg: '',
        },
        error: {
          toast: [],
          msg: '',
        }
      },
    }
  },
  mounted() {

    let toastSuccess = document.getElementById('live-toast-success');
    this.toasts.success.toast = Toast.getOrCreateInstance(toastSuccess);

    let toastError = document.getElementById('live-toast-error');
    this.toasts.error.toast = Toast.getOrCreateInstance(toastError);

    this.loadFaq();
  },
  methods: {

    /**
     * Chargement des données FAQ
     */
    loadFaq() {
      this.loading = true;
      axios.post(this.urls.load_faq, {
        'id': this.id
      }).then((response) => {
        this.faq = response.data.faq;
        this.loadData = true;
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });
    },

    /**
     * Permet de changer la locale pour la création/édition d'une page
     * @param event
     */
    switchLocale(event) {
      this.currentLocale = event.target.value;
      this.keyVal += 1;
    },

    /**
     * Retourne la valeur traduite en fonction de la locale pour le tableau d'élément
     * Ajoute contatValue au debut de la string avec un séparateur "-"
     * @param elements
     * @param property
     * @param concatValue
     * @returns {string}
     */
    getValueByLocale(elements, property, concatValue = "") {

      if (elements === undefined) {
        return "";
      }

      let str = '';
      elements.forEach((item) => {
        if (item.locale === this.currentLocale) {
          str = item[property];
          return true;
        }
      })
      if (concatValue !== "") {
        return str + "-" + concatValue;
      }
      return str;

    },

    /**
     * Mise à jour de la valeur en fonction de son id
     * @param value
     * @param id
     */
    updateValueByLocale(value, id) {
      let tmp = id.split('-');

      switch (tmp[1]) {
        case "faqTranslations":
          this.faq.faqTranslations.forEach((item) => {
            if (item.id === parseInt(tmp[0])) {
              item.title = value;
            }
          })
          break;
        case "faqCategoryTranslations":
          this.faq.faqCategories.forEach((faqC) => {
            faqC.faqCategoryTranslations.forEach((item) => {
              if (item.id === parseInt(tmp[0])) {
                item.title = value;
              }
            })
          })
          break;
        case "faqQuestionTranslations":
          this.faq.faqCategories.forEach((faqC) => {
            faqC.faqQuestions.forEach((faqQ) => {
              faqQ.faqQuestionTranslations.forEach((item) => {
                if (item.id === parseInt(tmp[0])) {
                  item[tmp[2]] = value;
                }
              })
            })
          })
          break;
        default:
      }

    },

    /**
     * Event depuis la sauvegarde du markdown
     * @param id
     * @param value
     */
    updateAnswer(id, value) {
      this.updateValueByLocale(value, id);
    },

    /**
     * Permet de sauvegarder une FAQ
     */
    save() {

      this.loading = true;
      axios.post(this.urls.save, {
        'faq': this.faq
      }).then((response) => {

        if (response.data.success === true) {
          this.toasts.success.msg = response.data.msg;
          this.toasts.success.toast.show();
        } else {
          this.toasts.error.msg = response.data.msg;
          this.toasts.error.toast.show();
        }

      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });
    },

    /**
     * Vérifie si le champ nouveau titre est vide ou non
     * @returns {boolean}
     */
    checkInputNewFaq() {
      let element = document.getElementById('new-title-faq');
      if (this.title === "" || this.title === null) {
        element.classList.add('is-invalid');
        return false;
      } else {
        element.classList.remove('is-invalid');
        return true;
      }
    },

    /**
     * Créer une nouvelle FAQ
     */
    newFaq() {

      if (!this.checkInputNewFaq()) {
        return false;
      }

      this.loading = true;
      axios.post(this.urls.new_faq, {
        'title': this.title
      }).then((response) => {

        if (response.data.success === true) {
          this.toasts.success.msg = response.data.msg;
          this.toasts.success.toast.show();
          window.location.replace(response.data.url_redirect);
        } else {
          this.toasts.error.msg = response.data.msg;
          this.toasts.error.toast.show();
          this.loading = false;
        }
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
      });
    }
  }
}
</script>

<template>


  <div id="block-faq" :class="this.loading === true ? 'block-grid' : ''">

    <div v-if="this.loading" class="overlay">
      <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1000;">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="txt-overlay">{{ this.translate.loading }}</span>
      </div>
    </div>

    <div v-if="this.id !== null">
      <!-- Edition d'un FAQ -->

      <select id="select-language" class="form-select float-end w-25" @change="this.switchLocale($event)">
        <option value="" selected>{{ this.translate.select_locale }}</option>
        <option v-for="(language, key) in this.locales.localesTranslate" :value="key"
            :selected="key===this.currentLocale">{{ language }}
        </option>
      </select>

      <div class="sticky-md-top float-start">
        <div class="btn btn-secondary" @click="this.save"><i class="bi bi-floppy-fill"></i> {{ this.translate.save }}
        </div>
      </div>
      <br/><br/><br/>


      <div v-if="this.loadData">

        <FieldEditor :key="this.keyVal"
            :id="this.getValueByLocale(this.faq.faqTranslations, 'id', 'faqTranslations')"
            :p-value="this.getValueByLocale(this.faq.faqTranslations, 'title')"
            balise="h1"
            rule="isEmpty"
            :rule-msg="this.translate.error_empty_value"
            @get-value="this.updateValueByLocale"
        />

        <div v-for="fcat in this.faq.faqCategories">

          <FieldEditor :key="this.keyVal"
              :id="this.getValueByLocale(fcat.faqCategoryTranslations, 'id', 'faqCategoryTranslations')"
              :p-value="this.getValueByLocale(fcat.faqCategoryTranslations, 'title')"
              balise="h3"
              rule="isEmpty"
              :rule-msg="this.translate.error_empty_value"
              @get-value="this.updateValueByLocale"
          />


          <div v-for="fQuestion in fcat.faqQuestions">

            <div class="card text-bg-light mt-2 mb-2">
              <div class="row">

                <div class="col-11">
                  <div class="card-body">

                    <FieldEditor :key="this.keyVal"
                        :id="this.getValueByLocale(fQuestion.faqQuestionTranslations, 'id', 'faqQuestionTranslations-title')"
                        :p-value="this.getValueByLocale(fQuestion.faqQuestionTranslations, 'title')"
                        balise="h5"
                        rule="isEmpty"
                        :rule-msg="this.translate.error_empty_value"
                        @get-value="this.updateValueByLocale"
                    />

                    <markdown-editor :key="this.keyVal"
                        :me-id="this.getValueByLocale(fQuestion.faqQuestionTranslations, 'id', 'faqQuestionTranslations-answer')"
                        :me-value="this.getValueByLocale(fQuestion.faqQuestionTranslations, 'answer')"
                        :me-rows="14"
                        :me-translate="this.translate.markdown"
                        :me-key-words="[]"
                        :me-save="true"
                        :me-preview="false"
                        @editor-value="this.updateAnswer"
                        @editor-value-change=""
                    />
                  </div>
                </div>
                <div class="col-1">
                  <i class="bi bi-arrow-up"></i>
                  <i class="bi bi-arrow-down"></i>
                </div>
              </div>
            </div>
          </div>
          <hr/>

        </div>
      </div>
      <!-- FIN Edition d'une FAQ -->
    </div>
    <div v-else>
      <!-- Création d'une FAQ -->
      <div class="card">
        <div class="card-header">
          {{ this.translate.new_faq }}
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="new-title-faq" class="form-label">{{ this.translate.new_faq_input_title }}</label>
            <input type="text" class="form-control no-control" id="new-title-faq" v-model="this.title"
                :placeholder="this.translate.new_faq_input_title" @change="checkInputNewFaq">
            <div class="invalid-feedback">
              {{ this.translate.new_faq_error_empty }}
            </div>
          </div>

          <p>
            <i class="bi bi-info-circle-fill"></i>
            <i class="ms-2"> {{ this.translate.new_faq_help }}</i>
          </p>

          <a href="#" class="btn btn-secondary" @click="newFaq">
            {{ this.translate.new_faq_btn }}
          </a>
        </div>
      </div>

      <!-- FIN Création d'une FAQ -->
    </div>
  </div>

  <!-- toast -->
  <div class="toast-container position-fixed top-0 end-0 p-2">

    <div id="live-toast-success" class="toast border border-secondary bg-white" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header text-success">
        <i class="bi bi-check-circle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast_title_success }}</strong>
        <small class="text-black">{{ this.translate.toast_time }}</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">
        {{ this.toasts.success.msg }}
      </div>
    </div>

    <div id="live-toast-error" class="toast border border-secondary bg-white" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header text-danger">
        <i class="bi bi-exclamation-triangle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast_title_error }}</strong>
        <small class="text-black">{{ this.translate.toast_time }}</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">
        {{ this.toasts.error.msg }}
      </div>
    </div>
  </div>

</template>