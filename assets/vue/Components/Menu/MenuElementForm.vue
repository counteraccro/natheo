<script lang="ts">
import { defineComponent, PropType, toRaw } from 'vue';
import { MenuElement, MenuElementTranslation, MenuFormTranslate } from '@/ts/Menu/type';

export default defineComponent({
  name: 'MenuElementForm',
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
