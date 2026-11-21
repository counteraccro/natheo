/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Thème Natheo-Studio, thème par défaut du back-office
 */
import { defineAdminTheme } from '@/vue/Composables/Admin/defineAdminTheme';
import NatheoStudioLayout from '@admin/Themes/NatheoStudio/NatheoStudioLayout.vue';

export default defineAdminTheme({
  id: 'natheo_studio',
  layout: NatheoStudioLayout,
  notFound: () => import('@admin/Themes/NatheoStudio/NatheoStudioNotFound.vue'),
  screens: {
    collection: () => import('@admin/Themes/NatheoStudio/Screens/CollectionScreen.vue'),
    form: () => import('@admin/Themes/NatheoStudio/Screens/FormScreen.vue'),
  },
});
