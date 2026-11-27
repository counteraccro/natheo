/*!
 * Author : Gourdon Aymeric
 * Version : 1.5
 * Point d'entrée de l'application Vue du back-office
 */
// Importée ici et non dans le CSS : Tailwind ne réécrit pas les chemins des fichiers de police
import '@fontsource-variable/figtree';
import './styles/app_admin_app.css';
import { createApp } from 'vue';
import AdminApp from '@admin/App/AdminApp.vue';
import { ADMIN_CONTEXT, createAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { createAdminRouter } from '@/vue/Composables/Admin/adminRouter';
import { loadAdminTheme, themeLoaders } from '@/vue/Composables/Admin/loadAdminTheme';
import { mergeModules, reloadMenuBadges, type AdminModuleDefinition } from '@/vue/Composables/Admin/defineAdminModule';
import type { AdminBootstrap } from '@/ts/Admin/App/AdminBootstrap.type';
import type { AdminTheme } from '@/ts/Admin/Theme/AdminTheme.type';

const bootstrap = JSON.parse(document.getElementById('admin-bootstrap')?.textContent ?? '{}') as AdminBootstrap;
const context = createAdminContext(bootstrap);

const modules = mergeModules(
  Object.values(
    import.meta.glob<AdminModuleDefinition>('./vue/Components/Admin/Modules/*/module.ts', {
      eager: true,
      import: 'default',
    })
  ),
  Object.values(
    import.meta.glob<AdminModuleDefinition>('./overwrite/vue/Components/Admin/Modules/*/module.ts', {
      eager: true,
      import: 'default',
    })
  )
);

reloadMenuBadges(modules, context.menu);

// Seul le thème de l'utilisateur est téléchargé, avec ses styles, avant le montage
const loaders = themeLoaders(
  import.meta.glob<AdminTheme>('./vue/Components/Admin/Themes/*/theme.ts', { import: 'default' }),
  import.meta.glob<AdminTheme>('./overwrite/vue/Components/Admin/Themes/*/theme.ts', { import: 'default' })
);

loadAdminTheme(bootstrap.theme, loaders).then((theme) => {
  const router = createAdminRouter({
    basePath: bootstrap.basePath,
    modules,
    debug: bootstrap.debug !== null,
    menu: context.menu,
    translator: context.translator,
    notFound: theme.notFound,
    pages: theme.pages,
    dashboardUrl: bootstrap.urls.dashboard,
    confirmLeave: () => context.leave.confirmLeave(),
  });

  createApp(AdminApp, { theme }).provide(ADMIN_CONTEXT, context).use(router).mount('#admin-app');
});
