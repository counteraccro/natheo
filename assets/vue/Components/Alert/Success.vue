<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Alert de type Success
 */
import { defineComponent, type PropType } from 'vue';
import AlertBase from './AlertBase.vue';
import type { AlertIcon } from '@/ts/Alert/types';
import type { ButtonItem } from '@/ts/Types/ButtonList.types';

export default defineComponent({
  name: 'AlertSuccess',
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
      type: 'alert-success-solid',
      icon: {
        viewBox: '0 0 20 20',
        path: 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z',
        fillRule: true,
      } as AlertIcon,
    };
  },
});
</script>

<template>
  <AlertBase :type="type" :text="text" :icon="icon" :buttons="buttons" />
</template>
