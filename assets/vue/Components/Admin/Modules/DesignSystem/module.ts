/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Catalogue des composants du design system, outil de dev
 */
import { defineAdminModule } from '@/vue/Composables/Admin/defineAdminModule';

export default defineAdminModule({
  id: 'design_system',
  debug: true,
  routes: [{ path: '/design-system', name: 'design_system', component: () => import('./DesignSystem.vue') }],
});
