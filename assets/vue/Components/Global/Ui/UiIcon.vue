<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Icône du registre (name) ou tracé au trait reçu de l'API (path) ; décorative sauf si label est donné
 */
import { computed } from 'vue';
import { ICONS, type IconName } from '@/vue/Composables/Global/icons';
import type { IconDefinition } from '@/ts/Global/Ui/UiIcon.type';

const props = defineProps<{
  name?: IconName;
  path?: string;
  label?: string;
}>();

const icon = computed<IconDefinition>(() => {
  if (props.name) {
    return ICONS[props.name];
  }
  return { paths: props.path ? [props.path] : [] };
});

const STROKE: Record<string, string> = {
  stroke: 'currentColor',
  'stroke-linecap': 'round',
  'stroke-linejoin': 'round',
  'stroke-width': '2',
};
const EVENODD: Record<string, string> = { 'fill-rule': 'evenodd', 'clip-rule': 'evenodd' };

const pathAttrs = computed((): Record<string, string> => {
  if (!icon.value.solid) {
    return STROKE;
  }
  return icon.value.evenodd ? EVENODD : {};
});
</script>

<template>
  <svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    :fill="icon.solid ? 'currentColor' : 'none'"
    :role="label ? 'img' : undefined"
    :aria-label="label"
    :aria-hidden="label ? undefined : 'true'"
    focusable="false"
    class="shrink-0"
  >
    <path v-for="d in icon.paths" :key="d" :d="d" v-bind="pathAttrs" />
  </svg>
</template>
