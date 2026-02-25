<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import axios from 'axios';
import { emitter } from '@/utils/useEvent';
import SkeletonFaq from '@/vue/Components/Skeleton/Faq.vue';
import Toast from '@/vue/Components/Global/Toast.vue';
import type { Faq, FaqCategory, FaqCategoryTranslation, FaqQuestion, FaqTranslation } from '@/ts/Faq/type';

type TranslateRecord = { [key: string]: string | TranslateRecord };

type Toast = {
  show: boolean;
  msg: string;
};

type Toasts = {
  [key: string]: Toast;
};

export default defineComponent({
  name: 'EditFaq',
  components: { Toast, SkeletonFaq },
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
      currentLocale: this.locales.current as string,
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
      return true;
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
          //this.tabMaxRenderOrder = response.data.max_render_order;
          //this.loadData = true;
          //this.keyVal += 1;
          emitter.emit('reset-check-confirm');
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    /**
     * Retourne la valeur traduite en fonction de la locale pour le tableau d'élément
     * Ajoute concatValue au debut de la string avec un séparateur "-"
     */
    getValueByLocale(elements: FaqTranslation[] | undefined, property: string, concatValue: string = ''): string {
      if (!elements) {
        return '';
      }

      const item = elements.find((item) => item.locale === this.currentLocale);
      const str = item ? item[property] : '';

      return concatValue !== '' ? `${str}-${concatValue}` : str;
    },

    /**
     * Mise à jour de la valeur en fonction de son id
     * @param value
     * @param id
     */
    updateValueByLocale(value: string, id: string): void {
      const tmp = id.split('-');
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
     * Permet de changer la locale pour la création/édition d'une page
     * @param event
     */
    switchLocale(event: any): void {
      this.currentLocale = event.target.value;
    },

    /**
     * Ferme le toast défini par nameToast
     * @param nameToast
     */
    closeToast(nameToast: string): void {
      this.toasts[nameToast].show = false;
    },
  },
});
</script>

<template>
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
      <button class="btn btn-sm btn-primary" :disabled="!checkIsNotEmpty">
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
    <div class="card rounded-lg p-6 mb-4 mt-4">
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
          <select id="select-language" class="form-input" @change="switchLocale($event)" style="width: 120px">
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

      <div class="form-group">
        <label class="form-label">{{ translate.input_faq_title }}</label>
        <input
          type="text"
          class="form-input"
          :class="getValueByLocale(faq.faqTranslations, 'title') === '' ? 'is-invalid' : ''"
          :value="getValueByLocale(faq.faqTranslations, 'title')"
          @change="
            updateValueByLocale($event.target.value, getValueByLocale(faq.faqTranslations, 'id', 'faqTranslations'))
          "
        />
        <span v-if="getValueByLocale(faq.faqTranslations, 'title') === ''" class="form-text text-error"
          >✗ {{ translate.input_faq_title_error }}</span
        >
        <span v-else class="form-text">{{ translate.input_faq_title_help }}</span>
      </div>
    </div>
  </div>

  <br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
  <br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
  <br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
  <br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
  <br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />

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
</template>

<style scoped></style>
