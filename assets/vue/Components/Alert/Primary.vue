<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Alert de type Important
 */
import { defineComponent, type PropType } from 'vue';
import AlertBase from './AlertBase.vue';
import type { AlertIcon } from '@/ts/Alert/types';
import type { ButtonItem } from '@/ts/Types/ButtonList.types';

export default defineComponent({
  name: 'AlertPrimary',
  components: { AlertBase },
  props: {
    text: {
      type: String as PropType<string>,
      default: '',
    },
    buttons: {
      type: Array as PropType<ButtonItem[]>,
      default: (): ButtonItem[] => [],
    },
  },
  data() {
    return {
      type: 'alert-primary-solid',
      icon: {
        viewBox: '0 0 24 24',
        path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        fillRule: false,
      } as AlertIcon,
    };
  },
});
</script>

<template>
  <AlertBase :type="type" :text="text" :icon="icon" :buttons="buttons" />
</template>
