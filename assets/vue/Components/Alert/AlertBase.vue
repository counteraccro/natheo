<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant de base pour les alertes (Primary, Success, Warning, Danger, Info)
 * Les variantes en héritent via `extends` et ne redéfinissent que leur nom
 * et les valeurs par défaut de `type` / `icon`.
 */
import { defineComponent, type PropType } from 'vue';
import type { AlertIcon } from '@/ts/Alert/types';
import type { ButtonItem } from '@/ts/Types/ButtonList.types';
import ButtonList from '@/vue/Components/Global/ButtonList.vue';

export default defineComponent({
  name: 'AlertBase',
  components: { ButtonList },
  props: {
    type: {
      type: String as PropType<string>,
      default: 'alert-primary-solid',
    },
    text: {
      type: String as PropType<string>,
      default: '',
    },
    icon: {
      type: Object as PropType<AlertIcon>,
      default: (): AlertIcon => ({
        viewBox: '0 0 24 24',
        path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        fillRule: false,
      }),
    },
    buttons: {
      type: Array as PropType<ButtonItem[]>,
      default: (): ButtonItem[] => [],
    },
  },
});
</script>

<template>
  <div class="alert" :class="type">
    <svg v-if="!icon.fillRule" class="alert-icon" fill="none" stroke="currentColor" :viewBox="icon.viewBox">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon.path"></path>
    </svg>
    <svg v-else class="alert-icon" fill="currentColor" :viewBox="icon.viewBox">
      <path fill-rule="evenodd" :d="icon.path" clip-rule="evenodd"></path>
    </svg>
    <div class="alert-content">
      <div class="alert-message" v-html="text"></div>
      <ButtonList v-if="buttons.length" class="mt-3" :buttons="buttons" />
    </div>
  </div>
</template>
