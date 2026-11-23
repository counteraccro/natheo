/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module Modules : activation des modules du back-office et de leurs opérations d'API
 */
import { defineAdminModule } from '@/vue/Composables/Admin/defineAdminModule';

export default defineAdminModule({
  id: 'module_activation',
  domains: ['module_activation'],
  routes: [
    {
      path: '/modules',
      name: 'module_activation_index',
      component: () => import('@admin/Modules/ModuleActivation/ModuleActivationScreen.vue'),
    },
  ],
});
