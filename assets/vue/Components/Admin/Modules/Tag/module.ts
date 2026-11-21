/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module Tags : liste, création et édition des tags
 */
import { collectionRoute, defineAdminModule, formRoute } from '@/vue/Composables/Admin/defineAdminModule';
import { tagCollection, tagForm } from '@admin/Modules/Tag/screens';

export default defineAdminModule({
  id: 'tag',
  domains: ['tag'],
  routes: [
    collectionRoute('/tags', 'tag_index', tagCollection),
    formRoute('/tags/new', 'tag_new', tagForm),
    formRoute('/tags/:id(\\d+)', 'tag_edit', tagForm, ['tag_index']),
  ],
});
