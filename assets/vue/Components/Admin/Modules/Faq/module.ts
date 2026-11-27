/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module FAQ : liste, création et édition des FAQ, avec l'arbre de leurs catégories et questions
 */
import { collectionRoute, defineAdminModule, formRoute } from '@/vue/Composables/Admin/defineAdminModule';
import { faqCollection, faqForm } from '@admin/Modules/Faq/screens';

export default defineAdminModule({
  id: 'faq',
  domains: ['faq'],
  routes: [
    collectionRoute('/faqs', 'faq_index', faqCollection),
    formRoute('/faqs/new', 'faq_new', faqForm),
    formRoute('/faqs/:id(\\d+)', 'faq_edit', faqForm, ['faq_index']),
  ],
});
