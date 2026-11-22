/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Thème Natheo-Atelier : navigation en haut, palette de commandes, liste et élément côte à côte
 */
import { defineAdminTheme } from '@/vue/Composables/Admin/defineAdminTheme';
import NatheoAtelierLayout from '@admin/Themes/NatheoAtelier/NatheoAtelierLayout.vue';

export default defineAdminTheme({
  id: 'natheo_atelier',
  layout: NatheoAtelierLayout,
  notFound: () => import('@admin/Layout/AdminNotFound.vue'),
  screens: {
    collection: () => import('@admin/Themes/NatheoAtelier/Screens/CollectionScreen.vue'),
    form: () => import('@admin/Themes/NatheoAtelier/Screens/FormScreen.vue'),
  },
});
