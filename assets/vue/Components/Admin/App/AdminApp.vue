<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Racine de l'application du back-office : libellés des composants, écran courant, notifications, confirmation unique
 * et départ d'un formulaire modifié
 */
import { onBeforeUnmount, onMounted } from 'vue';
import { RouterView, useRouter } from 'vue-router';
import { buildUiLabels, notify, provideUiLabels, UiConfirmDialog, UiToastRegion } from '@ui';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { useConfirm } from '@/vue/Composables/Admin/useConfirm';

const { t, leave } = useAdminContext();
const { current, answer } = useConfirm();

provideUiLabels(buildUiLabels(t));

// Menu ou catalogue de traduction illisible : la navigation est annulée
useRouter().onError((error: unknown) => {
  notify('danger', t('admin_app.error.navigation'));
  console.error(error);
});

onMounted(() => {
  document.addEventListener('click', leave.onClick);
  window.addEventListener('beforeunload', leave.onBeforeUnload);
});
onBeforeUnmount(() => {
  document.removeEventListener('click', leave.onClick);
  window.removeEventListener('beforeunload', leave.onBeforeUnload);
});
</script>

<template>
  <RouterView />
  <UiToastRegion />
  <UiConfirmDialog
    :open="current !== null"
    :title="current?.title ?? ''"
    :message="current?.message"
    :tone="current?.tone"
    :icon="current?.icon"
    :confirm-label="current?.confirmLabel"
    :confirm-icon="current?.confirmIcon"
    @confirm="answer(true)"
    @cancel="answer(false)"
  />
</template>
