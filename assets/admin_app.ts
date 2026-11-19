/*!
 * Author : Gourdon Aymeric
 * Version : 1.1
 * Point d'entrée de l'application Vue du back-office
 */
// Importée ici et non dans le CSS : Tailwind ne réécrit pas les chemins des fichiers de police
import '@fontsource-variable/figtree';
import './styles/app_admin_app.css';
import { createApp, defineComponent } from 'vue';
import AdminApp from '@/vue/Components/Admin/App/AdminApp.vue';
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
  menu: context.menu,
  translator: context.translator,
  notFound: defineComponent({ render: () => null }),
  dashboardUrl: bootstrap.urls.dashboard,
  confirmLeave: () => context.leave.confirmLeave(),
});

createApp(AdminApp).provide(ADMIN_CONTEXT, context).use(router).mount('#admin-app');
