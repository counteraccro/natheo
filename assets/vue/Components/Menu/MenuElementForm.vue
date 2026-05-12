<script lang="ts">
import { defineComponent, PropType, toRaw } from 'vue';
import { LoadMenuData, Locales, MenuElement, MenuElementTranslation, MenuFormTranslate } from '@/ts/Menu/type';
import Autocomplete, { AutocompleteOption } from '@/vue/Components/Global/AutoComplete.vue';

export default defineComponent({
  name: 'MenuElementForm',
  components: { Autocomplete },
  emit: ['cancel', 'save', 'delete', 'locale-validation', 'on-change'],
  props: {
    translate: {
      type: Object as PropType<MenuFormTranslate>,
      required: true,
    },
    menuElement: {
      type: Object as PropType<MenuElement>,
      required: true,
    },
    menuData: {
      type: Object as PropType<LoadMenuData>,
      required: true,
    },
    locale: {
      type: String,
      required: true,
    },
    locales: {
      type: Object as PropType<Locales>,
      required: true,
    },
  },

  data() {
    return {
      menuElementNoEdit: structuredClone(toRaw(this.menuElement)) as MenuElement,
      errors: {
        textLink: false,
        link: false,
      } as Record<string, boolean>,
    };
  },

  watch: {
    menuElement: {
      deep: true,
      handler() {
        this.$emit('on-change', this.menuElement.id);
      },
    },
    'menuElement.id'() {
      this.resetErrors();
      this.validate();
      this.menuElementNoEdit = structuredClone(toRaw(this.menuElement));
    },

    localeValidation: {
      immediate: true,
      deep: true,
      handler(val: Record<string, boolean>) {
        this.$emit('locale-validation', val);
      },
    },
    locale() {
      this.validate();
    },
  },

  computed: {
    /**
     * Retourne un objet autoCompleteOption
     */
    pageOptions(): AutocompleteOption[] {
      if (!this.menuData.pages) return [];

      return Object.entries(this.menuData.pages).map(([id, locales]) => ({
        value: Number(id),
        label: locales[this.locale]?.title ?? '',
        hint: locales[this.locale]?.url ?? '',
      }));
    },

    /**
     * Vérifie si la validation est bonne ou non
     */
    isValid(): boolean {
      const translation = this.menuElement.menuElementTranslations.find(({ locale }) => locale === this.locale);

      const hasTextLink = !!translation?.textLink?.trim();
      const hasPage = Number(this.menuElement.page) > 0;
      const hasExternalUrl = translation?.externalLink?.trim() !== '' && translation?.externalLink !== '#';

      return hasTextLink && (hasPage || hasExternalUrl);
    },

    /**
     * Retourne l'état de validation pour chaque locale
     */
    localeValidation(): Record<string, boolean> {
      const result: Record<string, boolean> = {};

      this.locales.locales.forEach((locale) => {
        const translation = this.menuElement.menuElementTranslations.find((t) => t.locale === locale);

        const hasTextLink = !!translation?.textLink?.trim();
        const hasPage = Number(this.menuElement.page) > 0;
        const hasExternalUrl = translation?.externalLink?.trim() !== '' && translation?.externalLink !== '#';

        result[locale] = hasTextLink && (hasPage || hasExternalUrl);
      });

      return result;
    },
  },

  methods: {
    /**
     * Retourne la valeur d'une traduction en fonction de la locale et d'une clé
     * @param tabMenuElementTranslation
     * @param key
     */
    getTranslationValueByKeyAndByLocale(
      tabMenuElementTranslation: MenuElementTranslation[],
      key: keyof MenuElementTranslation
    ): string {
      const translation = tabMenuElementTranslation.find(({ locale }) => locale === this.locale);
      return String(translation?.[key] ?? '');
    },

    /**
     * Met à jour la valeur d'une traduction en fonction de la locale et d'une clé
     * @param tabMenuElementTranslation
     * @param key
     * @param value
     */
    setTranslationValueByKeyAndByLocale(
      tabMenuElementTranslation: MenuElementTranslation[],
      key: keyof MenuElementTranslation,
      value: string
    ): void {
      const translation = tabMenuElementTranslation.find(({ locale }) => locale === this.locale);
      if (translation) {
        (translation[key] as string) = value;
      }
    },

    /**
     * On met à jour le lien en fonction de la page choisi
     * @param option
     */
    onPageSelect(option: AutocompleteOption): void {
      const translation = this.menuElement.menuElementTranslations.find(({ locale }) => locale === this.locale);
      if (translation) {
        translation.link = String(option.hint);
      }
    },

    /**
     * Change le type de lien
     * @param type
     */
    changeLinkType(type: '' | number) {
      this.menuElement.page = type;
      this.setTranslationValueByKeyAndByLocale(this.menuElement.menuElementTranslations, 'link', '0');
      this.setTranslationValueByKeyAndByLocale(this.menuElement.menuElementTranslations, 'externalLink', '#');
    },

    /**
     * Valide le formulaire avant sauvegarde
     */
    validate(): boolean {
      this.resetErrors();

      const translation = this.menuElement.menuElementTranslations.find(({ locale }) => locale === this.locale);

      if (!translation?.textLink?.trim()) {
        this.errors.textLink = true;
      }

      console.log(this.menuElement.page);

      const hasPage = Number(this.menuElement.page) > 0;

      console.log(hasPage);

      const hasExternalUrl = translation?.externalLink?.trim() !== '' && translation?.externalLink !== '#';

      if (!hasPage && !hasExternalUrl) {
        this.errors.link = true;
      }

      return !Object.values(this.errors).some(Boolean);
    },

    /**
     * Réinitialise les erreurs
     */
    resetErrors(): void {
      Object.keys(this.errors).forEach((key) => {
        this.errors[key] = false;
      });
    },

    onSave(): void {
      if (!this.validate()) return;
      this.$emit('save', this.menuElement, 'save');
    },
  },
});
</script>

<template>
  <div class="p-4 space-y-4">
    <div class="form-group">
      <label class="form-label" for="label-form-element">{{ translate.input_link_text }}</label>
      <input
        type="text"
        id="label-form-element"
        class="form-input"
        :class="errors.textLink ? 'is-invalid' : ''"
        :value="getTranslationValueByKeyAndByLocale(menuElement.menuElementTranslations, 'textLink')"
        @input="
          setTranslationValueByKeyAndByLocale(
            menuElement.menuElementTranslations,
            'textLink',
            ($event.target as HTMLInputElement).value
          )
        "
        @change="validate"
      />
      <span v-if="errors.textLink" class="form-text text-error">✗ {{ translate.empty_text_link_error }}</span>
    </div>

    <div>
      <label class="form-label">{{ translate.url_type_label }}</label>
      <div class="link-tabs" id="linkTypeTabs">
        <button class="link-tab" :class="Number(menuElement.page) >= 0 ? 'active' : ''" @click="changeLinkType(0)">
          {{ translate.radio_label_url_interne }}
        </button>
        <button class="link-tab" :class="Number(menuElement.page) < -1 ? 'active' : ''" @click="changeLinkType(-10)">
          {{ translate.radio_label_url_externe }}
        </button>
      </div>
    </div>

    <div v-if="Number(menuElement.page) > -1 || menuElement.page === null">
      <autocomplete
        v-model="menuElement.page"
        ref="searchInput"
        :options="pageOptions"
        :placeholder="translate.input_search_page_placeholder"
        :error-validate="translate.empty_internal_link_error"
        :empty-label="translate.input_search_page_placeholder_no_result"
        :has-error="errors.link"
        @change="validate"
        @select="onPageSelect"
      >
        <template #option="{ option }">
          <div class="flex items-center gap-3 w-full min-w-0">
            <!-- Icône -->
            <div
              class="shrink-0 w-7 h-7 rounded-md flex items-center justify-center"
              style="background-color: var(--primary-lighter)"
            >
              <svg
                class="w-3.5 h-3.5"
                style="color: var(--primary)"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                />
              </svg>
            </div>
            <!-- Label + URL -->
            <div class="flex flex-col min-w-0 flex-1">
              <span class="text-sm font-medium truncate" style="color: var(--text-primary)">{{ option.label }}</span>
              <span class="text-xs truncate" style="color: var(--text-light)">{{ option.hint }}</span>
            </div>
            <!-- ID badge -->
            <span
              class="shrink-0 text-xs font-semibold px-1.5 py-0.5 rounded"
              style="background-color: var(--primary-lighter); color: var(--primary)"
              >#{{ option.value }}</span
            >
          </div>
        </template>
      </autocomplete>
      <div class="form-group mt-3">
        <label class="form-label">{{ translate.input_link_url }}</label>
        <input
          type="text"
          class="form-input"
          :value="getTranslationValueByKeyAndByLocale(menuElement.menuElementTranslations, 'link')"
          disabled
        />
      </div>
    </div>
    <div v-else>
      <div class="form-group mt-3">
        <label class="form-label">{{ translate.input_link_external_url }}</label>
        <input
          type="text"
          class="form-input"
          :class="errors.link ? 'is-invalid' : ''"
          :value="getTranslationValueByKeyAndByLocale(menuElement.menuElementTranslations, 'externalLink')"
          @input="
            setTranslationValueByKeyAndByLocale(
              menuElement.menuElementTranslations,
              'externalLink',
              ($event.target as HTMLInputElement).value
            )
          "
          @change="validate"
        />
        <span v-if="errors.link" class="form-text text-error">✗ {{ translate.empty_text_link_error }}</span>
      </div>
    </div>

    <div class="form-group">
      <label for="liste-target" class="form-label">{{ translate.element_link_target_label }}</label>
      <select class="form-input" id="liste-target" v-model="menuElement.linkTarget">
        <option value="_self">{{ translate.element_link_target_label_self }}</option>
        <option value="_blank">{{ translate.element_link_target_label_blank }}</option>
      </select>
    </div>

    <div class="pt-2 border-t border-(--border-color)">
      <div class="form-switch form-switch-inline">
        <input
          class="switch-input no-control event-input"
          type="checkbox"
          role="switch"
          id="menuElement_disabled"
          :checked="!menuElement.disabled"
          @change="menuElement.disabled = !($event.target as HTMLInputElement).checked"
        />
        <label class="switch-toggle" for="menuElement_disabled"></label>
        <label class="swith-label" for="menuElement_disabled"
          ><span
            class="switch-label-text"
            v-html="
              menuElement.disabled ? translate.radio_label_disabled_element : translate.radio_label_enabled_element
            "
          ></span
        ></label>
      </div>

      <div class="text-end mb-5 text-sm text-(--text-secondary) italic">
        <span
          v-html="menuElement.disabled ? translate.text_info_disabled_element : translate.text_info_enabled_element"
        ></span>
      </div>
    </div>

    <div class="flex items-center justify-between pt-3 border-t" style="border-color: var(--border-color)">
      <button class="btn btn-sm btn-outline-danger" @click="$emit('delete', menuElement.id)">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
          ></path>
        </svg>
        {{ translate.btn_delete }}
      </button>
      <div class="flex gap-2">
        <button @click="$emit('cancel', menuElementNoEdit, 'cancel')" class="btn btn-sm btn-outline-dark">
          {{ translate.btn_cancel }}
        </button>
        <button class="btn btn-sm btn-primary" @click="onSave" :disabled="!isValid">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          {{ translate.btn_save }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
