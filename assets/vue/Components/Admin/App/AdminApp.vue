<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Racine de l'application du back-office : libellés des composants, layout du thème autour de l'écran courant,
 * notifications, confirmation unique, liens sans rechargement, départ d'un formulaire modifié et thème des écrans
 */
import { computed, onBeforeUnmount, onMounted, provide, watchEffect } from 'vue';
import { RouterView, useRoute, useRouter } from 'vue-router';
import { buildUiLabels, notify, provideUiLabels, UiConfirmDialog, UiToastRegion } from '@ui';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { useConfirm } from '@/vue/Composables/Admin/useConfirm';
import { createAdminLinks } from '@/vue/Composables/Admin/adminLinks';
import { NOT_FOUND_ROUTE } from '@/vue/Composables/Admin/adminRouter';
import { ADMIN_THEME } from '@/vue/Composables/Admin/Screen/adminTheme';
import type { AdminTheme } from '@/ts/Admin/Theme/AdminTheme.type';
import type { HeaderSection } from '@/ts/Admin/Layout/AdminHeader.type';

const props = defineProps<{ theme: AdminTheme }>();

const { t, leave, menu, bootstrap, page } = useAdminContext();
const { current, answer } = useConfirm();
const route = useRoute();
const router = useRouter();

provideUiLabels(buildUiLabels(t));
provide(ADMIN_THEME, props.theme);

const location = computed(() => ({
  name: typeof route.name === 'string' ? route.name : null,
  breadcrumb: route.meta.breadcrumb,
  page: page.value,
  query: route.query,
}));
const items = computed(() => menu.tree(location.value));
const shortcuts = computed(() => menu.shortcuts());
const section = computed<HeaderSection | null>(
  () =>
    menu.section(location.value) ?? (route.name === NOT_FOUND_ROUTE ? { label: t('admin_app.not_found.title') } : null)
);

// Même forme que les pages Twig : « titre – nom du site »
watchEffect(() => {
  const title = page.value?.title ?? section.value?.label;
  document.title = title ? `${title} – ${bootstrap.site.name}` : bootstrap.site.name;
});

// Menu ou catalogue de traduction illisible : la navigation est annulée
router.onError((error: unknown) => {
  notify('danger', t('admin_app.error.navigation'));
  console.error(error);
});

const onLinkClick = createAdminLinks(router, bootstrap.basePath);
onMounted(() => {
  // Ordre voulu : un lien de l'application passe par le routeur et sa garde, les autres par la confirmation
  document.addEventListener('click', onLinkClick);
  document.addEventListener('click', leave.onClick);
  window.addEventListener('beforeunload', leave.onBeforeUnload);
});
onBeforeUnmount(() => {
  document.removeEventListener('click', onLinkClick);
  document.removeEventListener('click', leave.onClick);
  window.removeEventListener('beforeunload', leave.onBeforeUnload);
});
</script>

<template>
  <component :is="theme.layout" :items="items" :section="section" :shortcuts="shortcuts" :bootstrap="bootstrap">
    <!-- Clé par chemin : des routes qui montent le même composant (liste, création, édition) le recréent -->
    <RouterView :key="route.path" />
  </component>
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
