<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Formulaire de création / édition d'une FAQ
 */
import axios from "axios";
import FieldEditor from "../../../../Components/Global/FieldEditor.vue";
import MarkdownEditor from "../../../../Components/Global/MarkdownEditor.vue";
import Modal from "../../../../Components/Global/Modal.vue";
import Toast from "../../../../Components/Global/Toast.vue";
import toast from "../../../../Components/Global/Toast.vue";

export default {
  name: "Faq",
  components: {
    MarkdownEditor,
    FieldEditor,
    Modal,
    Toast
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
      tabMaxRenderOrder: Object,
      currentLocale: this.locales.current,
      loading: false,
      loadData: false,
      keyVal: 1,
      title: '',
      modalTab: {
        newCatFaq: false,
        newQuestionFaq: false
      },
      toasts: {
        toastSuccessFaq: {
          show: false,
          msg: '',
        },
        toastErrorFaq: {
          show: false,
          msg: '',
        }
      },
    }
  },
  mounted() {

    /*let toastSuccess = document.getElementById('live-toast-success');
    this.toasts.success.toast = Toast.getOrCreateInstance(toastSuccess);

    let toastError = document.getElementById('live-toast-error');
    this.toasts.error.toast = Toast.getOrCreateInstance(toastError);*/

    if (this.id !== null) {
      this.loadFaq();
    }

  },
  methods: {

    /**
     * Chargement des données FAQ
     */
    loadFaq() {
      this.loading = true;
      axios.get(this.urls.load_faq + '/' + this.id).then((response) => {
        this.faq = response.data.faq;
        this.tabMaxRenderOrder = response.data.max_render_order;
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
      axios.put(this.urls.save, {
        'faq': this.faq
      }).then((response) => {

        if (response.data.success === true) {
          this.toasts.toastSuccessFaq.msg = response.data.msg;
          this.toasts.toastSuccessFaq.show = true;
        } else {
          this.toasts.toastErrorFaq.msg = response.data.msg;
          this.toasts.toastErrorFaq.show = true;
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
          this.toasts.toastSuccessFaq.msg = response.data.msg;
          this.toasts.toastSuccessFaq.show = true;
          window.location.replace(response.data.url_redirect);
        } else {
          this.toasts.toastErrorFaq.msg = response.data.msg;
          this.toasts.toastErrorFaq.show = true;
          this.loading = false;
        }
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
      });
    },

    /**
     * Active ou désactive un element en retournant la class disabled
     * @returns {string}
     */
    isDisabled() {
      if (this.loading) {
        return 'disabled';
      }
      return '';
    },

    /**
     * Affichage ou non un bouton en fonction de son type, idCat et de ordre de rendu
     * @param idCat
     * @param renderOrder
     * @param btnType
     * @returns {boolean}
     */
    showQuestionButton(idCat, renderOrder, btnType) {
      let r = true;
      this.tabMaxRenderOrder.max_render_order_questions.forEach((element) => {
        if (element.id_cat === idCat) {
          if (btnType === 'up') {
            r = renderOrder !== 1;
          } else {
            r = element.max_render !== renderOrder;
          }
        }
      })
      return r;
    },

    /**
     * Retourne une class bg en fonction si disabled est à true ou false
     * @param disabled
     * @returns {string}
     */
    getClassByDisabled(disabled) {
      if (disabled) {
        return 'bg-body-secondary'
      }
      return 'text-bg-light';
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

    closeToast(nameToast)
    {
      this.toasts[nameToast].show = false
    },

    test() {
      console.log('Yes');
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

      <div v-if="this.loadData">

        <div class="sticky-md-top p-3 mb-2 mt-2 bg-white border border-1 border-right rounded-1">
          <div class="row">
            <div class="col-9">
              <div class="btn btn-secondary" :class="isDisabled()" @click="this.save">
                <i class="bi bi-floppy-fill"></i> {{ this.translate.save }}
              </div>
              <div class="btn btn-secondary ms-3" :class="isDisabled()" @click="this.updateModale('newCatFaq', true)">
                <i class="bi bi-plus-square"></i> {{ this.translate.new_category_btn }}
              </div>
              <div class="btn btn-secondary ms-3" :class="isDisabled()" @click="this.updateModale('newQuestionFaq', true)">
                <i class="bi bi-question-square"></i> {{ this.translate.new_question_btn }}
              </div>
            </div>
            <div class="col-3">
              <select id="select-language" class="form-select" @change="this.switchLocale($event)">
                <option v-for="(language, key) in this.locales.localesTranslate" :value="key"
                    :selected="key===this.currentLocale">{{ language }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <FieldEditor :key="this.keyVal"
            :id="this.getValueByLocale(this.faq.faqTranslations, 'id', 'faqTranslations')"
            :p-value="this.getValueByLocale(this.faq.faqTranslations, 'title')"
            balise="h1"
            rule="isEmpty"
            :rule-msg="this.translate.error_empty_value"
            @get-value="this.updateValueByLocale"
        />

        <div v-for="fcat in this.faq.faqCategories">

          <div class="row">
            <div class="col-10">
              <FieldEditor :key="this.keyVal"
                  :id="this.getValueByLocale(fcat.faqCategoryTranslations, 'id', 'faqCategoryTranslations')"
                  :p-value="this.getValueByLocale(fcat.faqCategoryTranslations, 'title')"
                  balise="h3"
                  rule="isEmpty"
                  :rule-msg="this.translate.error_empty_value"
                  @get-value="this.updateValueByLocale"
              />
            </div>
            <div class="col-2">
              <div class="float-end">
                <div v-if="fcat.renderOrder !== 1" class="btn btn-secondary me-1"><i class="bi bi-arrow-up"></i></div>
                <div v-if="fcat.renderOrder !== this.tabMaxRenderOrder.max_render_order_category" class="btn btn-secondary me-1">
                  <i class="bi bi-arrow-down"></i></div>
                <div class="btn btn-secondary me-1"><i class="bi bi-trash"></i></div>
                <div v-if="fcat.disabled" class="btn btn-secondary me-1"><i class="bi bi-eye"></i></div>
                <div v-if="!fcat.disabled" class="btn btn-secondary me-1"><i class="bi bi-eye-slash"></i></div>
              </div>
            </div>
            <div v-if="fcat.disabled" class="float-end">
              <i>{{ this.translate.faq_category_disabled }}</i>
            </div>
          </div>

          <div v-for="fQuestion in fcat.faqQuestions">

            <div class="card mt-2 mb-2" :class="this.getClassByDisabled(fQuestion.disabled)">
              <div class="row align-items-center">

                <div class="col-11">
                  <div class="card-body">

                    <FieldEditor :key="this.keyVal" class="mb-3"
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

                    <div v-if="fQuestion.disabled" class="float-end">
                      <i>{{ this.translate.faq_question_disabled }}</i>
                    </div>

                  </div>
                </div>
                <div class="col-1">
                  <div v-if="this.showQuestionButton(fQuestion.faqCategory, fQuestion.renderOrder, 'up')" class="btn btn-secondary mt-1">
                    <i class="bi bi-arrow-up"></i>
                  </div>
                  <div class="clearfix"></div>
                  <div v-if="this.showQuestionButton(fQuestion.faqCategory, fQuestion.renderOrder, 'down')" class="btn btn-secondary mt-1">
                    <i class="bi bi-arrow-down"></i></div>
                  <div class="clearfix"></div>
                  <div class="btn btn-secondary mt-1"><i class="bi bi-trash"></i></div>
                  <div class="clearfix"></div>
                  <div v-if="fQuestion.disabled" class="btn btn-secondary mt-1"><i class="bi bi-eye"></i></div>
                  <div class="clearfix"></div>
                  <div v-if="!fQuestion.disabled" class="btn btn-secondary mt-1"><i class="bi bi-eye-slash"></i></div>
                </div>
              </div>
            </div>
          </div>
          <hr/>
        </div>
      </div>
      <!-- FIN Edition d'une FAQ -->

      <modal
          :id="'newCatFaq'"
          :show="this.modalTab.newCatFaq"
          @close-modal="this.closeModal"
          :option-modal-size="'modal-lg'"
      >
        <template #title>Nouvelle CAT</template>
        <template #body>
          <div class="btn btn-primary" @click="this.test">Test</div>
        </template>
        <template #footer>Ceci est le footer</template>
      </modal>

      <modal
          :id="'newQuestionFaq'"
          :show="this.modalTab.newQuestionFaq"
          @close-modal="this.closeModal"
          :option-show-close-btn="false">
        <template #title>Nouvelle Question</template>
        <template #body>
          <div class="btn btn-primary" @click="this.test">Test question FAQ</div>
        </template>
        <template #footer>Ceci est le footer</template>
      </modal>


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

    <toast
        :id="'toastSuccessFaq'"
        :option-class-header="'text-success'"
        :show="this.toasts.toastSuccessFaq.show"
        @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-check-circle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast_title_success }}</strong>
        <small class="text-black-50">{{ this.translate.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastSuccessFaq.msg"></div>
      </template>
    </toast>

    <toast
        :id="'toastErrorFaq'"
        :option-class-header="'text-danger'"
        :show="this.toasts.toastErrorFaq.show"
        @close-toast="this.closeToast"
    >
      <template #header>
        <i class="bi bi-exclamation-triangle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast_title_error }}</strong>
        <small class="text-black-50">{{ this.translate.toast_time }}</small>
      </template>
      <template #body>
        <div v-html="this.toasts.toastErrorFaq.msg"></div>
      </template>
    </toast>

   <!-- <div id="live-toast-success-2" class="toast border border-secondary bg-white" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header text-success">

        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">
        {{ this.toasts.success.msg }}
      </div>
    </div>

    <div id="live-toast-error-2" class="toast border border-secondary bg-white" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header text-danger">
        <i class="bi bi-exclamation-triangle-fill"></i> &nbsp;
        <strong class="me-auto"> {{ this.translate.toast_title_error }}</strong>
        <small class="text-black-50">{{ this.translate.toast_time }}</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">
        {{ this.toasts.error.msg }}
      </div>
    </div>-->


  </div>

</template>