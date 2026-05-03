<script lang="ts">
import { defineComponent, PropType, toRaw } from 'vue';
import { LoadMenuData, MenuDatas, MenuElement, MenuElementTranslation, MenuFormTranslate } from '@/ts/Menu/type';
import Autocomplete, { AutocompleteOption } from '@/vue/Components/Global/AutoComplete.vue';

export default defineComponent({
  name: 'MenuElementForm',
  components: { Autocomplete },
  emit: ['cancel', 'save', 'delete'],
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
  },

  data() {
    return {
      menuElementNoEdit: structuredClone(toRaw(this.menuElement)) as MenuElement,
    };
  },

  computed: {
    pageOptions(): AutocompleteOption[] {
      if (!this.menuData.pages) return [];

      return Object.entries(this.menuData.pages).map(([id, locales]) => ({
        value: Number(id),
        label: locales[this.locale]?.title ?? '',
        hint: locales[this.locale]?.url ?? '',
      }));
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

    changeLinkType(type: '' | number) {
      this.menuElement.page = type;
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
        :value="getTranslationValueByKeyAndByLocale(menuElement.menuElementTranslations, 'textLink')"
        @input="
          setTranslationValueByKeyAndByLocale(
            menuElement.menuElementTranslations,
            'textLink',
            ($event.target as HTMLInputElement).value
          )
        "
      />
    </div>

    <div>
      <label class="form-label">{{ translate.url_type_label }}</label>
      <div class="link-tabs" id="linkTypeTabs">
        <button class="link-tab" :class="menuElement.page !== '' ? 'active' : ''" @click="changeLinkType(0)">
          {{ translate.radio_label_url_interne }}
        </button>
        <button class="link-tab" :class="menuElement.page === '' ? 'active' : ''" @click="changeLinkType('')">
          {{ translate.radio_label_url_externe }}
        </button>
      </div>
    </div>

    <div v-if="menuElement.page !== ''">
      <autocomplete
        v-model="menuElement.page"
        ref="searchInput"
        :options="pageOptions"
        :placeholder="translate.input_search_page_placeholder"
        :empty-label="translate.input_search_page_placeholder_no_result"
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
        <button @click="$emit('cancel', menuElementNoEdit)" class="btn btn-sm btn-outline-dark">
          {{ translate.btn_cancel }}
        </button>
        <button class="btn btn-sm btn-primary" @click="$emit('save', menuElement)">
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
