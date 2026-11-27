/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Écrans du module FAQ : liste et formulaire, l'arbre des catégories et questions n'apparaissant qu'en édition
 */
import {
  defineCollectionScreen,
  defineFormScreen,
  translationKey as t,
} from '@/vue/Composables/Admin/Screen/defineScreen';
import type { AdminFaq } from '@/ts/Admin/Api/AdminFaq.type';

const RESOURCE = '/admin/faqs';

export const faqCollection = defineCollectionScreen<AdminFaq>({
  resource: RESOURCE,
  title: t('faq.collection.title'),
  description: t('faq.collection.description'),
  icon: 'info',
  read: { operation: 'admin_faq_get_collection' },
  columns: [
    { key: 'id', label: t('faq.collection.column_id'), sortable: true, hidden: false },
    { key: 'title', label: t('faq.collection.column_title'), sortable: true },
    { key: 'categoryCount', label: t('faq.collection.column_categories'), type: 'number', hideBelow: 'md' },
    { key: 'questionCount', label: t('faq.collection.column_questions'), type: 'number', hideBelow: 'md' },
    { key: 'pageCount', label: t('faq.collection.column_pages'), type: 'number', hideBelow: 'lg' },
    { key: 'author', label: t('faq.collection.column_author'), type: 'author', hideBelow: 'lg' },
    { key: 'updatedAt', label: t('faq.collection.column_updated_at'), sortable: true, type: 'date', hideBelow: 'lg' },
  ],
  filters: ['status', 'author'],
  create: { route: 'faq_new', label: t('faq.collection.button_new'), operation: 'admin_faq_post' },
  actions: [
    { type: 'edit', route: 'faq_edit', operation: 'admin_faq_patch' },
    { type: 'toggle', operation: 'admin_faq_patch' },
    { type: 'delete', operation: 'admin_faq_delete' },
  ],
  bulkActions: [
    { type: 'disable', operation: 'admin_faq_patch' },
    { type: 'enable', operation: 'admin_faq_patch' },
    { type: 'delete', operation: 'admin_faq_delete' },
  ],
  rowLabel: (faq) => faq.title,
  deletable: (faq) => faq.pageCount === 0,
  messages: {
    confirmDisable: t('faq.dialog.confirm_disable'),
    confirmDelete: t('faq.dialog.confirm_delete'),
    disabled: t('faq.notify.disabled'),
    enabled: t('faq.notify.enabled'),
    deleted: t('faq.notify.deleted'),
  },
});

export const faqForm = defineFormScreen<AdminFaq>({
  resource: RESOURCE,
  title: { create: t('faq.editor.title_new'), edit: t('faq.editor.title_edit'), view: t('faq.editor.title_view') },
  description: { create: t('faq.editor.description_new'), edit: t('faq.editor.description_edit') },
  icon: 'info',
  back: 'faq_index',
  editRoute: 'faq_edit',
  leaveKey: 'faq',
  initial: () => ({ disabled: false, translations: [] }),
  sections: [
    {
      title: t('faq.editor.section_title'),
      description: t('faq.editor.section_title_description'),
      kind: 'label',
      fields: [
        {
          type: 'translations',
          name: 'translations',
          field: 'title',
          label: t('faq.editor.title'),
          maxlength: 255,
          copyFromCurrent: true,
        },
      ],
    },
    {
      title: t('faq.editor.section_content'),
      description: t('faq.editor.section_content_description'),
      kind: 'content',
      on: 'edit',
      fields: [
        {
          type: 'component',
          name: 'categories',
          component: () => import('@admin/Modules/Faq/FaqContentField.vue'),
        },
      ],
    },
    {
      title: t('faq.editor.section_status'),
      description: t('faq.editor.section_status_description'),
      kind: 'status',
      fields: [
        { type: 'switch', name: 'disabled', label: t('faq.editor.disabled'), help: t('faq.editor.disabled_help') },
      ],
    },
  ],
  meta: ['createdAt', 'updatedAt', 'author', 'pageCount'],
  read: { operation: 'admin_faq_get' },
  create: { operation: 'admin_faq_post' },
  update: { operation: 'admin_faq_patch' },
  delete: { operation: 'admin_faq_delete' },
  messages: {
    created: t('faq.notify.created'),
    saved: t('faq.notify.saved'),
    confirmDelete: t('faq.dialog.confirm_delete'),
    deleted: t('faq.notify.deleted'),
  },
  rowLabel: (faq) => faq.title || faq.translations[0]?.title || '',
});
