/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module Commentaires : liste, modération groupée et modération d'un commentaire
 */
import { collectionRoute, defineAdminModule, formRoute } from '@/vue/Composables/Admin/defineAdminModule';
import { commentCollection, commentForm } from '@admin/Modules/Comment/screens';

export default defineAdminModule({
  id: 'comment',
  domains: ['comment'],
  badges: ['/admin/comments'],
  routes: [
    collectionRoute('/comments', 'comment_index', commentCollection),
    formRoute('/comments/:id(\\d+)', 'comment_edit', commentForm, ['comment_index']),
  ],
});
