<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Formulaire de création / édition d'une FAQ
 */
import axios from "axios";
import FieldEditor from "../../../../Components/Global/FieldEditor.vue";

export default {
  name: "Faq",
  components: {
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
      keyVal: 1
    }
  },
  mounted() {
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
      console.log(tmp);

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

                  <div>{{ this.getValueByLocale(fQuestion.faqQuestionTranslations, 'answer') }}</div>
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

</template>