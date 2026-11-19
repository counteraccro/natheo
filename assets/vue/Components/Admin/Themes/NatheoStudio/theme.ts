/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Thème Natheo-Studio, thème par défaut du back-office
 */
import { defineAdminTheme } from '@/vue/Composables/Admin/defineAdminTheme';
import NatheoStudioLayout from './NatheoStudioLayout.vue';

export default defineAdminTheme({
  id: 'natheo_studio',
  layout: NatheoStudioLayout,
  notFound: () => import('./NatheoStudioNotFound.vue'),
});
