<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Entrées du menu utilisateur des thèmes : mon compte, mes options, déconnexion
 */
import UiDropdownItem from '@admin/Ui/Overlay/UiDropdownItem.vue';
import UiDropdownSeparator from '@admin/Ui/Overlay/UiDropdownSeparator.vue';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';

const { t, bootstrap } = useAdminContext();
</script>

<template>
  <UiDropdownItem icon="user" :href="bootstrap.urls.myAccount">{{
    t('admin_app.user_menu.my_account')
  }}</UiDropdownItem>
  <UiDropdownItem icon="sliders" :href="bootstrap.urls.myOptions">{{
    t('admin_app.user_menu.my_options')
  }}</UiDropdownItem>
  <UiDropdownSeparator />
  <UiDropdownItem icon="logout" :href="bootstrap.urls.logout">{{ t('admin_app.user_menu.logout') }}</UiDropdownItem>
</template>
