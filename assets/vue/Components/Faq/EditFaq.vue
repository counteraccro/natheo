<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import { emitter } from '@/utils/useEvent';
import SkeletonFaq from '@/vue/Components/Skeleton/Faq.vue';
import Toast from '@/vue/Components/Global/Toast.vue';
import type {
  Faq,
  FaqCategory,
  FaqCategoryTranslation,
  FaqQuestion,
  FaqQuestionTranslation,
  FaqTranslation,
} from '@/ts/Faq/type';
import Sortable from 'sortablejs';
import MarkdownEditor from '@/vue/Components/Global/MarkdownEditor/MarkdownEditor.vue';
import { EditorModule } from '@/ts/MarkdownEditor/MarkdownEditor.types';
import { InternalLinkModule } from '@/ts/MarkdownEditor/modules/internalLink';
import Modal from '@/vue/Components/Global/Modal.vue';
import { Toasts } from '@/ts/Toast/type';

type TranslateRecord = { [key: string]: string | TranslateRecord };

export default defineComponent({
  name: 'EditFaq',
  components: { Modal, MarkdownEditor, Toast, SkeletonFaq },
  props: {
    urls: { type: Object as PropType<Record<string, string>>, required: true },
    translate: { type: Object as PropType<TranslateRecord>, required: true },
    locales: { type: Object as PropType<Record<string, string>>, required: true },
    id: Number,
  },
  data() {
    return {
      loading: false,
      updateNoSave: false,
      showModalConfirmDelete: false,
      currentLocale: this.locales.current as string,
      openQuestions: new Set() as Set<number>,
      keyMarkdownEditor: 0 as number,
      tempIdCounter: -1 as number,
      modale: {
        title: null,
        body: null,
        params: {
          type: '',
          id: 0,
          isConfirm: false,
        },
      },
      faq: {} as Faq,
      toasts: {
        success: {
          show: false,
          msg: '',
        },
        error: {
          show: false,
          msg: '',
        },
      } as Toasts,
    };
  },

  setup(): any {
    const editorModules: EditorModule[] = [InternalLinkModule];
    return {
      editorModules,
    };
  },

  mounted(): any {
    this.loadFaq();
  },
  computed: {
    /**
     * check si les champs ne sont pas vide avant sauvegarde
     */
    checkIsNotEmpty(): boolean {
      if (this.getValueByLocale(this.faq.faqTranslations, 'title') === '') {
        return false;
      }

      let valReturn = true;

      if (this.faq.faqCategories.length === 0) {
        valReturn = false;
      }

      this.faq.faqCategories.forEach((category) => {
        if (this.getValueByLocale(category.faqCategoryTranslations, 'title') === '') {
          valReturn = false;
        }
      });

      this.faq.faqCategories.forEach((category) => {
        if (category.faqQuestions.length === 0) {
          valReturn = false;
        }

        category.faqQuestions.forEach((question) => {
          if (this.getValueByLocale(question.faqQuestionTranslations, 'title') === '') {
            valReturn = false;
          }

          if (this.getValueByLocale(question.faqQuestionTranslations, 'answer') === '') {
            valReturn = false;
          }
        });
      });

      return valReturn;
    },
  },
  methods: {
    /**
     * Chargement des données FAQ
     */
    loadFaq() {
      this.loading = true;
      axios
        .get(this.urls.load_faq + '/' + this.id)
        .then((response) => {
          this.faq = response.data.faq;
          emitter.emit('reset-check-confirm');
          this.keyMarkdownEditor += 1;
          this.loading = false;
          this.loadDraggableCategories();
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {});
    },

    /**
     * drag&Drop des catégories
     */
    loadDraggableCategories(): void {
      this.$nextTick(() => {
        Sortable.create(this.$refs.categoriesListRef, {
          handle: '.handle',
          animation: 200,
          ghostClass: 'opacity-40',
          chosenClass: 'ring-2',
          onEnd: ({ item, from, oldIndex, newIndex }) => {
            from.insertBefore(item, from.children[oldIndex] || null);
            const items = [...this.faq.faqCategories];
            const [moved] = items.splice(oldIndex, 1);
            items.splice(newIndex, 0, moved);

            items.forEach((item, index) => {
              item.renderOrder = index + 1;
            });

            this.faq.faqCategories = items;
            this.updateNoSave = true;
          },
        });

        this.loadDraggableQuestions();
      });
    },

    loadDraggableQuestions(): void {
      const questionLists = this.$refs.rootRef.querySelectorAll('.questions-list');
      questionLists.forEach((list) => {
        Sortable.create(list, {
          group: 'questions',
          handle: '.handle-question',
          animation: 200,
          ghostClass: 'opacity-40',
          onEnd: ({ item, from, to, oldIndex, newIndex }) => {
            const fromCatId = parseInt(from.dataset.catId);
            const toCatId = parseInt(to.dataset.catId);

            const fromCat = this.faq.faqCategories.find((c) => c.id === fromCatId);
            const toCat = this.faq.faqCategories.find((c) => c.id === toCatId);

            // SortableJS a déjà déplacé le nœud DOM,
            // on annule ce mouvement et on laisse Vue gérer
            from.insertBefore(item, from.children[oldIndex] || null);

            // Déplace la question dans le tableau
            const [moved] = fromCat.faqQuestions.splice(oldIndex, 1);
            toCat.faqQuestions.splice(newIndex, 0, moved);

            // Réassigne renderOrder dans les deux catégories impactées
            fromCat.faqQuestions.forEach((q, i) => {
              q.renderOrder = i + 1;
            });
            toCat.faqQuestions.forEach((q, i) => {
              q.renderOrder = i + 1;
            });

            this.updateNoSave = true;
          },
        });
      });
    },

    /**
     * Retourne la valeur traduite en fonction de la locale pour le tableau d'élément
     * Ajoute concatValue au debut de la string avec un séparateur "-"
     */
    getValueByLocale(
      elements: FaqTranslation[] | FaqCategoryTranslation[] | FaqQuestionTranslation[] | undefined,
      property: string,
      concatValue: string = ''
    ): string {
      if (!elements) {
        return '';
      }

      const item = elements.find((item) => item.locale === this.currentLocale);
      const str = item ? item[property] : '';

      return concatValue !== '' ? `${str}|${concatValue}` : str;
    },

    /**
     * Mise à jour de la valeur en fonction de son id
     * @param value
     * @param id
     */
    updateValueByLocale(value: string, id: string): void {
      const tmp = id.split('|');
      const itemId = parseInt(tmp[0]);
      this.updateNoSave = true;

      switch (tmp[1]) {
        case 'faqTranslations':
          const faqTranslation = this.faq.faqTranslations.find((item) => item.id === itemId);
          if (faqTranslation) faqTranslation.title = value;
          break;

        case 'faqCategoryTranslations':
          this.faq.faqCategories.forEach((faqC) => {
            const faqCategoryTranslation = faqC.faqCategoryTranslations.find((item) => item.id === itemId);
            if (faqCategoryTranslation) faqCategoryTranslation.title = value;
          });
          break;

        case 'faqQuestionTranslations':
          this.faq.faqCategories.forEach((faqC) => {
            faqC.faqQuestions.forEach((faqQ) => {
              const item = faqQ.faqQuestionTranslations.find((item) => item.id === itemId);
              if (item) {
                item[tmp[2]] = value;
              }
            });
          });
          break;
      }
    },

    /**
     * Affiche le bon nombre de questions
     * @param questions
     */
    getNbQuestion(questions: FaqQuestion[]): string {
      const nbQuestions = questions.length;
      switch (nbQuestions) {
        case 0:
          return this.translate.no_questions.toString();
        case 1:
          return this.translate.one_question.toString();
        default:
          return nbQuestions + ' ' + this.translate.multiple_questions.toString();
      }
    },

    toggleQuestion(id: number): void {
      if (this.openQuestions.has(id)) {
        this.openQuestions.delete(id);
      } else {
        this.openQuestions.add(id);
      }
      // Force la réactivité car Set n'est pas nativement réactif
      this.openQuestions = new Set(this.openQuestions);
    },

    /**
     * Event depuis la sauvegarde du markdown
     * @param id
     * @param value
     */
    updateAnswer(id: string, value: string): void {
      this.updateValueByLocale(value, id);
    },

    /**
     * Permet de sauvegarder une FAQ
     */
    save(): void {
      this.loading = true;
      axios
        .put(this.urls.save, {
          faq: this.faq,
        })
        .then((response) => {
          if (response.data.success === true) {
            this.toasts.success.msg = response.data.msg;
            this.toasts.success.show = true;
            this.updateNoSave = false;
          } else {
            this.toasts.error.msg = response.data.msg;
            this.toasts.error.show = true;
          }
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loadFaq();
        });
    },

    /**
     * Ajoute une nouvelle question
     * @param categoryId
     */
    addQuestion(categoryId: number): void {
      const category = this.faq.faqCategories.find((c) => c.id === categoryId);
      if (!category) return;

      const translations: FaqQuestionTranslation[] = Object.keys(this.locales.localesTranslate).map((locale) => ({
        id: this.tempIdCounter--,
        FaqQuestion: this.tempIdCounter,
        locale: locale,
        title: this.translate[`new_question_${locale}`],
        answer: this.translate[`new_answer_${locale}`],
      }));

      const newQuestion: FaqQuestion = {
        id: this.tempIdCounter--,
        faqCategory: categoryId,
        disabled: category.disabled,
        renderOrder: category.faqQuestions.length + 1,
        faqQuestionTranslations: translations,
      };

      category.faqQuestions.push(newQuestion);

      this.openQuestions.add(newQuestion.id);
      this.openQuestions = new Set(this.openQuestions);

      this.updateNoSave = true;
    },

    /**
     * Ajoute une nouvelle catégorie
     */
    addCategory(): void {
      const translations: FaqCategoryTranslation[] = Object.keys(this.locales.localesTranslate).map((locale) => ({
        id: this.tempIdCounter--,
        faqCategory: this.tempIdCounter,
        locale: locale,
        title: this.translate[`new_category_${locale}`],
      }));

      const newCategory: FaqCategory = {
        id: this.tempIdCounter--,
        faq: this.faq.id,
        disabled: false,
        renderOrder: this.faq.faqCategories.length + 1,
        faqCategoryTranslations: translations,
        faqQuestions: [],
      };

      this.faq.faqCategories.push(newCategory);
      this.updateNoSave = true;

      this.$nextTick(() => {
        this.loadDraggableCategories();
      });
    },

    /**
     * Désactive une question / faq / catégorie en fonction du type
     * @param type
     * @param id
     */
    disabled(type: string, id: number) {
      switch (type) {
        case 'category':
          const item = this.faq.faqCategories.find((item) => item.id === id);
          if (item) {
            item.disabled = !item.disabled;

            if (item.disabled) {
              item.faqQuestions.forEach((question) => {
                question.disabled = true;
              });
            }
            this.updateNoSave = true;
          }
          break;
        case 'question':
          this.faq.faqCategories.forEach((faqC) => {
            const item = faqC.faqQuestions.find((item) => item.id === id);
            if (item) {
              item.disabled = !item.disabled;
              this.updateNoSave = true;
            }
          });
          break;
      }
    },

    deleteElement(type: string, id: number, isConfirm: boolean) {
      this.showModalConfirmDelete = false;
      if (!isConfirm) {
        switch (type) {
          case 'category':
            this.modale.title = this.translate.modale_confirm_delete_category_title;
            this.modale.body = this.translate.modale_confirm_delete_category_body;
            break;
          case 'question':
            this.modale.title = this.translate.modale_confirm_delete_question_title;
            this.modale.body = this.translate.modale_confirm_delete_question_body;
            break;
        }

        this.modale.params.type = type;
        this.modale.params.id = id;
        this.modale.params.isConfirm = true;

        this.showModalConfirmDelete = true;
        return;
      }

      switch (type) {
        case 'category':
          this.faq.faqCategories = this.faq.faqCategories.filter((c) => c.id !== id);
          this.faq.faqCategories.forEach((c, i) => {
            c.renderOrder = i + 1;
          });
          break;
        case 'question':
          this.faq.faqCategories.forEach((faqC) => {
            faqC.faqQuestions = faqC.faqQuestions.filter((q) => q.id !== id);
            faqC.faqQuestions.forEach((q, i) => {
              q.renderOrder = i + 1;
            });
          });

          this.openQuestions.delete(id);
          this.openQuestions = new Set(this.openQuestions);
          break;
        default:
          return;
      }

      this.updateNoSave = true;
    },

    /**
     * Permet de changer la locale pour la création/édition d'une page
     * @param event
     */
    switchLocale(event: any): void {
      this.currentLocale = event.target.value;
      this.keyMarkdownEditor += 1;
    },

    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast: string): void {
      this.toasts[nameToast].show = false;
    },

    /**
     * Ferme la modale
     */
    hideModal() {
      this.showModalConfirmDelete = false;
    },
  },
});
</script>

<template>
  <div ref="rootRef">
    <div
      v-if="!loading"
      class="card rounded-lg p-3 mb-4 mt-4 flex justify-between items-center sticky top-0 z-10"
      :style="
        !checkIsNotEmpty
          ? 'background-color: var(--alert-danger-bg); color: var(--alert-danger-text);border: 2px solid var(--alert-danger-border);'
          : updateNoSave
            ? 'background-color: var(--alert-warning-bg); color: var(--alert-warning-text); border: 2px solid var(--alert-warning-border);'
            : ''
      "
    >
      <div class="pl-4">
        <span v-if="!checkIsNotEmpty" class="text-sm italic flex items-center gap-1">
          <svg
            class="w-5 h-5"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            />
          </svg>

          {{ translate.text_error }}
        </span>
        <span v-else-if="updateNoSave" class="text-sm italic flex items-center gap-1">
          <svg
            class="w-5 h-5"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            />
          </svg>

          {{ translate.text_change_no_save }}</span
        >
        <span class="text-sm text-[var(--text-secondary)] italic flex items-center gap-1" v-else>
          <svg
            class="w-5 h-5"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            />
          </svg>

          {{ translate.text_no_change }}</span
        >
      </div>

      <div class="flex justify-end gap-2">
        <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.history.back()">
          <svg
            class="icon"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            ></path>
          </svg>
          {{ translate.btn_cancel }}
        </button>
        <button class="btn btn-sm btn-primary" :disabled="!checkIsNotEmpty" @click="save()">
          <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
          </svg>
          {{ translate.btn_save }}
        </button>
      </div>
    </div>

    <div v-if="loading" class="mt-5">
      <SkeletonFaq :is-new="false" />
    </div>
    <div v-else>
      <div
        class="border rounded-lg p-6 mb-4 mt-4"
        :class="faq.disabled ? 'border-2 border-[var(--alert-warning-border)]' : 'border-[var(--border-color)]'"
      >
        <div class="border-b-1 border-b-[var(--border-color)] mb-4 flex justify-between items-start">
          <div>
            <h2 class="text-lg font-bold text-[var(--text-primary)]">{{ translate.edit_faq }}</h2>
            <p class="text-sm mt-1 mb-3 text-[var(--text-secondary)]">{{ translate.edit_faq_sub_title }}</p>
          </div>
          <div class="input-addon-group">
            <span class="input-addon input-addon-left">
              <svg
                class="icon"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                fill="none"
                viewBox="0 0 24 24"
              >
                <path
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="m13 19 3.5-9 3.5 9m-6.125-2h5.25M3 7h7m0 0h2m-2 0c0 1.63-.793 3.926-2.239 5.655M7.5 6.818V5m.261 7.655C6.79 13.82 5.521 14.725 4 15m3.761-2.345L5 10m2.761 2.655L10.2 15"
                />
              </svg>
            </span>
            <select
              id="select-language"
              class="form-input"
              @change="switchLocale($event)"
              style="width: 120px"
              :disabled="!checkIsNotEmpty"
            >
              <option
                v-for="(language, key) in locales.localesTranslate"
                :value="key"
                :selected="String(key) === currentLocale"
              >
                {{ language }}
              </option>
            </select>
          </div>
        </div>

        <div class="form-group mb-3">
          <div class="form-switch form-switch-inline">
            <input
              class="switch-input no-control event-input"
              type="checkbox"
              role="switch"
              id="disabled_faq"
              :checked="!faq.disabled"
              @change="
                faq.disabled = !($event.target as HTMLInputElement).checked;
                updateNoSave = true;
              "
            />
            <label class="switch-toggle" for="disabled_faq"></label>
            <label class="swith-label" for="disabled_faq"
              ><span class="switch-label-text"> {{ translate.input_disabled_faq_label }} </span></label
            >
          </div>
          <span v-if="faq.disabled" class="form-text text-warning"
            >⚠ {{ translate.input_disabled_faq_help_disabled }}</span
          >
          <span v-else class="form-text">
            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              ></path>
            </svg>
            {{ translate.input_disabled_faq_help }}
          </span>
        </div>

        <div class="form-group">
          <label class="form-label">{{ translate.input_faq_title }}</label>
          <input
            type="text"
            class="form-input"
            :class="getValueByLocale(faq.faqTranslations, 'title') === '' ? 'is-invalid' : ''"
            :value="getValueByLocale(faq.faqTranslations, 'title')"
            @change="
              updateValueByLocale(
                ($event.target as HTMLInputElement).value,
                getValueByLocale(faq.faqTranslations, 'id', 'faqTranslations')
              )
            "
          />
          <span v-if="getValueByLocale(faq.faqTranslations, 'title') === ''" class="form-text text-error"
            >✗ {{ translate.input_faq_title_error }}</span
          >
          <span v-else class="form-text">
            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              ></path>
            </svg>
            {{ translate.input_faq_title_help }}</span
          >
        </div>
      </div>
      <div ref="categoriesListRef" class="space-y-4">
        <div
          v-for="category in faq.faqCategories"
          :key="category.id"
          class="border rounded-lg mb-4 mt-4"
          :class="
            category.faqQuestions.length === 0
              ? 'border-2 border-[var(--alert-danger-border)]'
              : category.disabled
                ? 'border-2 border-[var(--alert-warning-border)]'
                : 'border-[var(--border-color)]'
          "
          style="box-shadow: var(--shadow-sm)"
        >
          <div
            class="flex items-center gap-2 px-4 py-3 border-b border-[var(--border-color)] rounded-t-lg"
            :style="
              category.faqQuestions.length === 0
                ? 'background: linear-gradient(90deg, var(--alert-danger-bg) 0%, transparent 60%)'
                : category.disabled
                  ? 'background: linear-gradient(90deg, var(--alert-warning-bg) 0%, transparent 60%)'
                  : 'background: linear-gradient(90deg, var(--primary-lighter) 0%, transparent 60%)'
            "
          >
            <span class="handle text-[var(--text-light)] hover:text-[var(--primary)] cursor-grab p-0.5 rounded">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 12h16M4 16h16"></path>
              </svg>
            </span>

            <span
              class="text-[0.65rem] font-mono text-[var(--text-light)] w-5 flex-shrink-0"
              v-html="category.renderOrder > 9 ? category.renderOrder : '0' + category.renderOrder"
            ></span>

            <span
              v-if="category.disabled"
              class="inline-flex items-center gap-1 text-[0.6rem] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full flex-shrink-0"
              style="background-color: var(--btn-warning); color: white"
            >
              <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                ></path>
              </svg>
              {{ translate.disabled_label }}
            </span>

            <input
              type="text"
              :class="
                getValueByLocale(category.faqCategoryTranslations, 'title') === ''
                  ? 'border border-[var(--error)] placeholder:text-[var(--error)] focus:border-[var(--error)]'
                  : 'border-b border-transparent focus:border-[var(--primary)]'
              "
              class="flex-1 bg-transparent outline-none py-0.5 min-w-0 text-sm font-bold text-[var(--text-primary)]"
              :value="getValueByLocale(category.faqCategoryTranslations, 'title')"
              :placeholder="translate.input_faq_title_error.toString()"
              @change="
                updateValueByLocale(
                  ($event.target as HTMLInputElement).value,
                  getValueByLocale(category.faqCategoryTranslations, 'id', 'faqCategoryTranslations')
                )
              "
            />

            <span
              class="text-[0.65rem] font-bold font-mono rounded-full px-2.5 py-0.5 flex-shrink-0"
              style="background: var(--primary-lighter); color: var(--primary)"
              v-html="getNbQuestion(category.faqQuestions)"
            ></span>

            <button
              @click="deleteElement('category', category.id, false)"
              class="p-1.5 rounded-md text-[var(--text-light)] hover:text-[var(--btn-danger)] hover:bg-red-50"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                ></path>
              </svg>
            </button>
            <button
              v-if="category.disabled"
              @click="disabled('category', category.id)"
              class="p-1 rounded text-[var(--text-light)] hover:text-[var(--primary)] hover:bg-[var(--primary-lighter)]"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path
                  stroke="currentColor"
                  stroke-width="2"
                  d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z"
                ></path>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
              </svg>
            </button>
            <button
              v-else
              @click="disabled('category', category.id)"
              class="p-1 rounded text-[var(--text-light)] hover:text-[var(--primary)] hover:bg-[var(--primary-lighter)]"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                ></path>
              </svg>
            </button>
          </div>

          <div class="questions-list p-3 space-y-2" :data-cat-id="category.id">
            <div v-if="category.faqQuestions.length === 0">
              <div class="text-center py-2 text-[var(--text-light)]">
                <svg
                  class="w-8 h-8 mx-auto mb-2 opacity-30"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.5"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  ></path>
                </svg>
                <p class="text-xs">{{ translate.text_error_not_question }}</p>
              </div>
            </div>

            <div
              v-for="question in category.faqQuestions"
              :key="question.id"
              class="border rounded-lg mb-2 overflow-hidden"
              :class="
                getValueByLocale(question.faqQuestionTranslations, 'title') === '' ||
                getValueByLocale(question.faqQuestionTranslations, 'answer') === ''
                  ? 'border-2 border-[var(--alert-danger-border)]'
                  : question.disabled
                    ? 'border-2 border-[var(--alert-warning-border)]'
                    : 'border-[var(--border-color)]'
              "
            >
              <div
                class="flex items-center gap-2.5 px-3 py-2.5"
                :class="
                  getValueByLocale(question.faqQuestionTranslations, 'title') === '' ||
                  getValueByLocale(question.faqQuestionTranslations, 'answer') === ''
                    ? 'bg-[var(--alert-danger-bg)]'
                    : question.disabled
                      ? 'bg-[var(--alert-warning-bg)]'
                      : 'bg-[var(--bg-card)]'
                "
              >
                <span
                  class="handle-question cursor-grab text-[var(--text-light)] hover:text-[var(--primary)] flex-shrink-0"
                  @click.stop
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 12h16M4 16h16" />
                  </svg>
                </span>

                <span
                  class="text-[0.65rem] font-mono text-[var(--text-light)] w-5 flex-shrink-0"
                  v-html="question.renderOrder > 9 ? question.renderOrder : '0' + question.renderOrder"
                ></span>

                <span
                  v-if="question.disabled"
                  class="inline-flex items-center gap-1 text-[0.6rem] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full flex-shrink-0"
                  style="background-color: var(--btn-warning); color: white"
                >
                  <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                    ></path>
                  </svg>
                  {{ translate.disabled_label }}
                </span>

                <!-- Titre cliquable pour le toggle -->
                <div
                  class="flex flex-1 items-center justify-between cursor-pointer"
                  @click="toggleQuestion(question.id)"
                >
                  <span
                    class="text-sm font-medium truncate"
                    :class="
                      getValueByLocale(question.faqQuestionTranslations, 'title') === '' ||
                      getValueByLocale(question.faqQuestionTranslations, 'answer') === ''
                        ? 'text-[var(--alert-danger-text)]'
                        : 'text-[var(--text-primary)]'
                    "
                    v-html="
                      getValueByLocale(question.faqQuestionTranslations, 'title') === '' ||
                      getValueByLocale(question.faqQuestionTranslations, 'answer') === ''
                        ? translate.question_error
                        : getValueByLocale(question.faqQuestionTranslations, 'title')
                    "
                  >
                  </span>

                  <!-- Chevron -->
                  <svg
                    class="w-4 h-4 flex-shrink-0 text-[var(--text-light)] transition-transform duration-200"
                    :class="openQuestions.has(question.id) ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
                <button
                  @click="deleteElement('question', question.id, false)"
                  class="p-1 rounded text-[var(--text-light)] hover:text-[var(--btn-danger)] hover:bg-red-50"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    ></path>
                  </svg>
                </button>
                <button
                  v-if="question.disabled"
                  @click="disabled('question', question.id)"
                  class="p-1 rounded text-[var(--text-light)] hover:text-[var(--primary)] hover:bg-[var(--primary-lighter)]"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path
                      stroke="currentColor"
                      stroke-width="2"
                      d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z"
                    ></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                  </svg>
                </button>
                <button
                  v-else
                  @click="disabled('question', question.id)"
                  class="p-1 rounded text-[var(--text-light)] hover:text-[var(--primary)] hover:bg-[var(--primary-lighter)]"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                    ></path>
                  </svg>
                </button>
              </div>

              <!-- Contenu accordéon -->
              <div
                v-if="openQuestions.has(question.id)"
                class="border-t border-[var(--border-color)] bg-[var(--bg-hover)] px-4 py-4 space-y-3"
              >
                <div>
                  <label
                    class="flex items-center gap-1.5 text-[0.65rem] font-bold uppercase tracking-widest text-[var(--text-secondary)] mb-1.5"
                  >
                    <svg
                      class="w-3 h-3"
                      style="color: var(--primary)"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2.5"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                      ></path>
                    </svg>
                    {{ translate.question_label }}
                  </label>
                  <input
                    type="text"
                    class="form-input"
                    :class="getValueByLocale(question.faqQuestionTranslations, 'title') === '' ? 'is-invalid' : ''"
                    :value="getValueByLocale(question.faqQuestionTranslations, 'title')"
                    @change="
                      updateValueByLocale(
                        ($event.target as HTMLInputElement).value,
                        getValueByLocale(question.faqQuestionTranslations, 'id', 'faqQuestionTranslations|title')
                      )
                    "
                  />
                </div>
                <div>
                  <label
                    class="flex items-center gap-1.5 text-[0.65rem] font-bold uppercase tracking-widest text-[var(--text-secondary)] mb-1.5"
                  >
                    <svg
                      class="w-3 h-3"
                      style="color: var(--primary)"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2.5"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"
                      ></path>
                    </svg>
                    {{ translate.answer_label }}
                  </label>
                  <markdown-editor
                    :key="keyMarkdownEditor"
                    :me-id="getValueByLocale(question.faqQuestionTranslations, 'id', 'faqQuestionTranslations|answer')"
                    :me-value="getValueByLocale(question.faqQuestionTranslations, 'answer')"
                    :me-rows="14"
                    :me-translate="translate.markdown"
                    :me-modules="editorModules"
                    :me-key-words="[]"
                    :me-save="true"
                    :me-preview="false"
                    :me-required="true"
                    @editor-value="updateAnswer"
                    @editor-value-change=""
                  />
                </div>
              </div>
            </div>
          </div>

          <div class="p-3 pt-0 space-y-2">
            <button
              @click="addQuestion(category.id)"
              class="w-full flex items-center gap-2 px-3 py-2 border-2 border-dashed border-[var(--border-color)] rounded-lg text-xs font-medium text-[var(--text-light)] hover:border-[var(--primary)] hover:text-[var(--primary)] hover:bg-[var(--primary-lighter)] cursor-pointer"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
              </svg>
              {{ translate.btn_new_question }}
            </button>
          </div>
        </div>
      </div>
      <button
        @click="addCategory()"
        :class="
          !faq.faqCategories || faq.faqCategories.length === 0
            ? 'border-[var(--alert-danger-border)] text-[var(--alert-danger-text)] bg-[var(--alert-danger-bg)]'
            : 'border-[var(--border-color)] text-[var(--text-secondary)] hover:border-[var(--primary)] hover:text-[var(--primary)] hover:bg-[var(--primary-lighter)]'
        "
        class="w-full flex items-center justify-center gap-2 px-4 py-4 border-2 border-dashed rounded-xl text-sm font-semibold cursor-pointer"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
        </svg>
        {{ translate.btn_new_category }}
      </button>
    </div>

    <modal
      :id="'confirm-delete-element'"
      :show="showModalConfirmDelete"
      @close-modal="hideModal"
      :option-show-close-btn="false"
    >
      <template #title> {{ modale.title }}</template>
      <template #body>
        <div v-html="modale.body"></div>
      </template>
      <template #footer>
        <button
          type="button"
          class="btn btn-primary btn-sm me-2"
          @click="deleteElement(modale.params.type, modale.params.id, modale.params.isConfirm)"
        >
          <svg
            class="icon"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            />
          </svg>
          {{ translate.btn_confirm_ok }}
        </button>
        <button type="button" class="btn btn-outline-dark btn-sm" @click="hideModal()">
          <svg
            class="icon"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
            />
          </svg>

          {{ translate.btn_confirm_ko }}
        </button>
      </template>
    </modal>

    <div class="toast-container position-fixed top-0 end-0 p-2">
      <toast :id="'toastSuccess'" :type="'success'" :show="toasts.success.show" @close-toast="closeToast('success')">
        <template #body>
          <div v-html="toasts.success.msg"></div>
        </template>
      </toast>

      <toast :id="'toastError'" :type="'danger'" :show="toasts.error.show" @close-toast="closeToast('error')">
        <template #body>
          <div v-html="toasts.error.msg"></div>
        </template>
      </toast>
    </div>
  </div>
</template>

<style scoped></style>
