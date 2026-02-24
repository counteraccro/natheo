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
  computed: {},
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
      //this.updateNoSave = true;

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
        <label class="form-label">{{ translate.new_faq_input_title }}</label>
        <input
          type="text"
          class="form-input"
          :value="getValueByLocale(faq.faqTranslations, 'title')"
          @change="
            updateValueByLocale($event.target.value, getValueByLocale(faq.faqTranslations, 'id', 'faqTranslations'))
          "
        />
        <span class="form-text text-error">✗ {{ translate.new_faq_error_empty }}</span>
        <span class="form-text">{{ translate.new_faq_help }}</span>
      </div>
    </div>
  </div>

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
