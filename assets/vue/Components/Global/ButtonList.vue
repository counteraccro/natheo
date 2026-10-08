<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Affiche une liste dynamique de boutons à partir d'un tableau de configuration
 */
import { defineComponent, type PropType } from 'vue';
import type { ButtonItem } from '@/ts/Types/ButtonList.type';

export default defineComponent({
  name: 'ButtonList',
  props: {
    buttons: {
      type: Array as PropType<ButtonItem[]>,
      required: true,
    },
  },
  methods: {
    handleClick(button: ButtonItem): void {
      if (button.disabled) {
        return;
      }
      if (button.params && button.params.length) {
        button.onClick(...button.params);
      } else {
        button.onClick();
      }
    },
  },
});
</script>

<template>
  <div class="btn-showcase">
    <button
      class="mr-2"
      v-for="button in buttons"
      :key="button.id"
      :class="button.css"
      :disabled="button.disabled"
      @click="handleClick(button)"
    >
      {{ button.label }}
    </button>
  </div>
</template>
