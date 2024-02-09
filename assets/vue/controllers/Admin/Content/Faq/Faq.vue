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
      toasts: {
        save: {
          toast: [],
          msg: '',
          bg: 'bg-success'
        },
      }
    }
  },
  mounted() {

    let toastAutoSave = document.getElementById('live-toast-save');
    this.toasts.save.toast = Toast.getOrCreateInstance(toastAutoSave);

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
        this.toasts.save.msg = this.translate.msg_save_success;
        this.toasts.save.toast.show();
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        // On lance le rechargement du render
        this.loading = false;
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

    <select id="select-language" class="form-select float-end w-25" @change="this.switchLocale($event)">
      <option value="" selected>{{ this.translate.select_locale }}</option>
      <option v-for="(language, key) in this.locales.localesTranslate" :value="key"
          :selected="key===this.currentLocale">{{ language }}
      </option>
    </select>

    <div class="sticky-md-top float-start">
      <div class="btn btn-secondary" @click="this.save"><i class="bi bi-floppy-fill"></i> {{ this.translate.save }}</div>
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
  </div>

  <!-- toast -->
  <div class="toast-container position-fixed top-0 end-0 p-2">
    <div id="live-toast-save" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-body text-white" :class="this.toasts.save.bg">
        <i class="bi bi-check-circle-fill"></i>
        {{ this.toasts.save.msg }}
      </div>
    </div>
  </div>

</template>