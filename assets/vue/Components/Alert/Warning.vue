<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Alert de type Warning
 */
import { defineComponent, type PropType } from 'vue';
import AlertBase from './AlertBase.vue';
import type { AlertIcon } from '@/ts/Alert/Alert.type';
import type { ButtonItem } from '@/ts/Types/ButtonList.type';

export default defineComponent({
  name: 'AlertWarning',
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
      type: 'alert-warning-solid',
      icon: {
        viewBox: '0 0 24 24',
        path: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        fillRule: false,
      } as AlertIcon,
    };
  },
});
</script>

<template>
  <AlertBase :type="type" :text="text" :icon="icon" :buttons="buttons" />
</template>
