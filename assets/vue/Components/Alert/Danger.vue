<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Alert de type Danger
 */
import { defineComponent, type PropType } from 'vue';
import AlertBase from './AlertBase.vue';
import type { AlertIcon } from '@/ts/Alert/types';
import type { ButtonItem } from '@/ts/Types/ButtonList.types';

export default defineComponent({
  name: 'AlertDanger',
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
      type: 'alert-danger-solid',
      icon: {
        viewBox: '0 0 24 24',
        path: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        fillRule: false,
      } as AlertIcon,
    };
  },
});
</script>

<template>
  <AlertBase :type="type" :text="text" :icon="icon" :buttons="buttons" />
</template>
