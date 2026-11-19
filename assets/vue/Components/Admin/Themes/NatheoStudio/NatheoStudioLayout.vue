<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Layout du thème Natheo-Studio : sidebar du registre, en-tête (section, recherche, « Nouveau », apparence,
 * notifications, menu utilisateur), écran courant et barre de debug en dev
 */
import { computed } from 'vue';
import { AdminDebugBar, AdminHeader, AdminShell, AdminSidebar, type DebugSection, type DebugUser } from '@ui';
import NatheoStudioUserMenu from './NatheoStudioUserMenu.vue';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { roleLabel } from '@/vue/Composables/Admin/roleLabel';
import type { AdminLayoutProps } from '@/ts/Admin/Theme/AdminTheme.type';

const props = defineProps<AdminLayoutProps>();
defineSlots<{ default?: () => unknown }>();

const { t, leave } = useAdminContext();

const user = computed(() => ({
  name: props.bootstrap.user.name,
  detail: roleLabel(props.bootstrap.user.roles, t),
  initials: props.bootstrap.user.initials,
}));

const impersonating = computed(() => {
  const { user: current, urls } = props.bootstrap;
  return current.impersonating !== null && urls.impersonationExit !== null
    ? { name: current.impersonating, exitHref: urls.impersonationExit }
    : undefined;
});

const debugSections = computed<DebugSection[]>(() => {
  const debug = props.bootstrap.debug;
  if (debug === null) return [];
  return [
    {
      title: t('admin_app.debug.system'),
      entries: [
        { label: t('admin_app.debug.php'), value: debug.php },
        { label: t('admin_app.debug.environment'), value: debug.env },
        { label: t('admin_app.debug.database'), value: debug.database },
      ],
    },
    {
      title: t('admin_app.debug.git'),
      entries: [
        { label: t('admin_app.debug.branch'), value: debug.branch ?? '' },
        { label: t('admin_app.debug.commit'), value: debug.commit ?? '' },
        { label: t('admin_app.debug.commit_date'), value: debug.commitDate ?? '' },
      ],
    },
    {
      title: t('admin_app.debug.application'),
      entries: [{ label: t('admin_app.debug.version'), value: debug.version }],
    },
  ];
});

const debugUsers = computed<DebugUser[]>(() =>
  (props.bootstrap.debug?.users ?? []).map((debugUser) => ({
    name: debugUser.login,
    detail: roleLabel([debugUser.role], t),
    initials: debugUser.login.slice(0, 2).toUpperCase(),
    href: debugUser.url,
    current: debugUser.id === props.bootstrap.user.id,
  }))
);

/**
 * Recherche globale : envoyée à la page Twig des résultats, qui recharge
 * @param term
 */
const submitSearch = async (term: string): Promise<void> => {
  if (term.trim() === '' || !(await leave.confirmLeave())) return;
  const form = document.createElement('form');
  form.method = 'post';
  form.action = props.bootstrap.urls.search;
  const input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'global-search-input';
  input.value = term;
  form.append(input);
  document.body.append(form);
  form.submit();
};

const openNotifications = async (): Promise<void> => {
  if (await leave.confirmLeave()) {
    window.location.assign(props.bootstrap.urls.notifications);
  }
};
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
        @open-nav="openNav"
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
