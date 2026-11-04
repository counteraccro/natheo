<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Pile des toasts de la page, montée une seule fois dans la base Twig (admin et installation)
 */

import { defineComponent } from 'vue';
import Toast from '@/vue/Components/Global/Toast.vue';
import { closeToast, toastState } from '@/vue/Composables/Global/toast';

export default defineComponent({
  name: 'ToastContainer',
  components: { Toast },
  data() {
    return { toastState };
  },
  methods: {
    closeToast,
  },
});
</script>

<template>
  <div class="fixed top-5 right-5 z-[100] flex flex-col gap-2">
    <Toast
      v-for="item in toastState.items"
      :key="item.id"
      :type="item.type"
      :msg="item.msg"
      @close="closeToast(item.id)"
    />
  </div>
</template>
