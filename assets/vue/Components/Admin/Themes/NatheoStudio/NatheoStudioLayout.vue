<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Layout du thème Natheo-Studio : sidebar du registre, en-tête (section, recherche, « Nouveau », apparence et disposition,
 * notifications, menu utilisateur), écran courant et barre de debug en dev
 */
import { AdminDebugBar, AdminHeader, AdminShell, AdminSidebar } from '@ui';
import NatheoStudioUserMenu from '@admin/Themes/NatheoStudio/NatheoStudioUserMenu.vue';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { useLayoutActions } from '@/vue/Composables/Admin/Layout/useLayoutActions';
import type { AdminLayoutProps } from '@/ts/Admin/Theme/AdminTheme.type';

const props = defineProps<AdminLayoutProps>();
defineSlots<{ default?: () => unknown }>();

const { t } = useAdminContext();
const { user, impersonating, debugSections, debugUsers, submitSearch, openNotifications, themeChoice } =
  useLayoutActions(props.bootstrap);
</script>

<template>
  <AdminShell>
    <template #sidebar="{ close, mobile }">
      <AdminSidebar
        :items="items"
        :site-name="bootstrap.site.name"
        :subtitle="t('admin_app.layout.subtitle')"
        :version="bootstrap.site.version"
        :user="user"
        :closable="mobile"
        @close="close"
        @navigate="close"
      >
        <template #user-menu><NatheoStudioUserMenu /></template>
      </AdminSidebar>
    </template>
    <template #header="{ openNav }">
      <AdminHeader
        :section="section ?? undefined"
        :shortcuts="shortcuts"
        :user-initials="bootstrap.user.initials"
        :impersonating="impersonating"
        :notifiable="bootstrap.options.notifications"
        :layouts="bootstrap.themes"
        :layout="bootstrap.theme"
        @open-nav="openNav"
        @select-layout="themeChoice.choose"
        @submit-search="submitSearch"
        @open-notifications="openNotifications"
      >
        <template #user-menu><NatheoStudioUserMenu /></template>
      </AdminHeader>
    </template>

    <slot />

    <AdminDebugBar
      v-if="bootstrap.debug"
      :env="bootstrap.debug.env"
      :branch="bootstrap.debug.branch ?? undefined"
      :sections="debugSections"
      :users="debugUsers"
    />
  </AdminShell>
</template>
