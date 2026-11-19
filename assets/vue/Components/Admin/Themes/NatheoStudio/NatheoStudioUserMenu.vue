<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Entrées du menu utilisateur, dans la sidebar et dans l'en-tête
 */
import { UiDropdownItem, UiDropdownSeparator } from '@ui';
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
