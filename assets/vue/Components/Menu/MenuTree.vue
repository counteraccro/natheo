<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MenuElement, MenuElementTranslation, MenuTreeTranslate } from '@/ts/Menu/type';

export default defineComponent({
  name: 'MenuTree',
  props: {
    translate: {
      type: Object as PropType<MenuTreeTranslate>,
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
    deep: {
      type: Number,
      required: true,
    },
    idSelected: {
      type: Number,
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
  },
});
</script>

<template>
  <div class="tree-node">
    <div class="tree-node-row">
      <span class="drag-handle">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
          <path
            d="M8 6a2 2 0 100-4 2 2 0 000 4zM16 6a2 2 0 100-4 2 2 0 000 4zM8 14a2 2 0 100-4 2 2 0 000 4zM16 14a2 2 0 100-4 2 2 0 000 4zM8 22a2 2 0 100-4 2 2 0 000 4zM16 22a2 2 0 100-4 2 2 0 000 4z"
          ></path>
        </svg>
      </span>
      <button class="tree-toggle open">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
        </svg>
      </button>
      <span class="tree-node-label">{{
        getTranslationValueByKeyAndByLocale(menuElement.menuElementTranslations, 'textLink')
      }}</span>
      <span class="node-type-badge badge-page">{{ translate.tag_page }}</span>
      <div class="tree-node-actions">
        <button class="tree-action-btn add" data-tooltip="Ajouter un enfant">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
        </button>
        <button class="tree-action-btn edit" data-tooltip="Éditer">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
            ></path>
          </svg>
        </button>
        <button class="tree-action-btn hide" data-tooltip="Masquer">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
            ></path>
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
            ></path>
          </svg>
        </button>
        <button class="tree-action-btn delete" data-tooltip="Supprimer">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
            ></path>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
