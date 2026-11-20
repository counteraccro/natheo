/*!
 * Author : Gourdon Aymeric
 * Version : 1.2
 * Point d'entrée de l'application Vue du back-office
 */
// Importée ici et non dans le CSS : Tailwind ne réécrit pas les chemins des fichiers de police
import '@fontsource-variable/figtree';
import './styles/app_admin_app.css';
import { createApp } from 'vue';
import AdminApp from '@admin/App/AdminApp.vue';
import theme from '@admin/Themes/NatheoStudio/theme';
import { ADMIN_CONTEXT, createAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { createAdminRouter } from '@/vue/Composables/Admin/adminRouter';
import type { AdminModuleDefinition } from '@/vue/Composables/Admin/defineAdminModule';
import type { AdminBootstrap } from '@/ts/Admin/App/AdminBootstrap.type';

const bootstrap = JSON.parse(document.getElementById('admin-bootstrap')?.textContent ?? '{}') as AdminBootstrap;
const context = createAdminContext(bootstrap);

const modules = Object.values(
  import.meta.glob<AdminModuleDefinition>('./vue/Components/Admin/Modules/*/module.ts', {
    eager: true,
    import: 'default',
  })
);

const router = createAdminRouter({
  basePath: bootstrap.basePath,
  modules,
  debug: bootstrap.debug !== null,
  menu: context.menu,
  translator: context.translator,
  notFound: theme.notFound,
  dashboardUrl: bootstrap.urls.dashboard,
  confirmLeave: () => context.leave.confirmLeave(),
});

createApp(AdminApp, { theme }).provide(ADMIN_CONTEXT, context).use(router).mount('#admin-app');
