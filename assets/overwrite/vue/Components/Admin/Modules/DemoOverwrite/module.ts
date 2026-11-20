/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Exemple de surcharge : écran déclaré depuis assets/overwrite/, module PHP dans src/Overwrite/Module/
 */
import { defineAdminModule } from '@/vue/Composables/Admin/defineAdminModule';

export default defineAdminModule({
  id: 'demo_overwrite',
  domains: ['demo_overwrite'],
  routes: [
    {
      path: '/demo-overwrite',
      name: 'demo_overwrite',
      component: () => import('@admin/Modules/DemoOverwrite/DemoOverwrite.vue'),
    },
  ],
});
