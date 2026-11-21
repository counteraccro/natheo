/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Écrans du module Tags : liste et formulaire
 */
import {
  defineCollectionScreen,
  defineFormScreen,
  translationKey as t,
} from '@/vue/Composables/Admin/Screen/defineScreen';
import type { AdminTag } from '@/ts/Admin/Api/AdminTag.type';

const RESOURCE = '/admin/tags';

/** Couleur #rrggbb tirée au hasard */
const randomColor = (): string =>
  '#' +
  Math.floor(Math.random() * 0x1000000)
    .toString(16)
    .padStart(6, '0');

export const tagCollection = defineCollectionScreen<AdminTag>({
  resource: RESOURCE,
  title: t('tag.collection.title'),
  description: t('tag.collection.description'),
  icon: 'tag',
  columns: [
    { key: 'id', label: t('tag.collection.column_id'), sortable: true, hidden: false },
    { key: 'label', label: t('tag.collection.column_label'), sortable: true, type: 'badge', color: 'color' },
    { key: 'pageCount', label: t('tag.collection.column_pages'), type: 'number', hideBelow: 'md' },
    { key: 'author', label: t('tag.collection.column_author'), type: 'author', hideBelow: 'lg' },
    { key: 'updatedAt', label: t('tag.collection.column_updated_at'), sortable: true, type: 'date', hideBelow: 'lg' },
  ],
  filters: ['status', 'author'],
  create: { route: 'tag_new', label: t('tag.collection.button_new'), operation: 'admin_tag_post' },
  actions: [
    { type: 'edit', route: 'tag_edit', operation: 'admin_tag_patch' },
    { type: 'toggle', operation: 'admin_tag_patch' },
    { type: 'delete', operation: 'admin_tag_delete' },
  ],
  bulkActions: [
    { type: 'disable', operation: 'admin_tag_patch' },
    { type: 'enable', operation: 'admin_tag_patch' },
    { type: 'delete', operation: 'admin_tag_delete' },
  ],
  rowLabel: (tag) => tag.label,
  deletable: (tag) => tag.pageCount === 0,
  messages: {
    confirmDisable: t('tag.dialog.confirm_disable'),
    confirmDelete: t('tag.dialog.confirm_delete'),
    disabled: t('tag.notify.disabled'),
    enabled: t('tag.notify.enabled'),
    deleted: t('tag.notify.deleted'),
  },
});

export const tagForm = defineFormScreen<AdminTag>({
  resource: RESOURCE,
  title: { create: t('tag.editor.title_new'), edit: t('tag.editor.title_edit') },
  icon: 'tag',
  back: 'tag_index',
  editRoute: 'tag_edit',
  leaveKey: 'tag',
  initial: () => ({ color: randomColor(), disabled: false, translations: [] }),
  sections: [
    {
      title: t('tag.editor.section_label'),
      fields: [
        {
          type: 'translations',
          name: 'translations',
          field: 'label',
          label: t('tag.editor.label'),
          maxlength: 255,
          copyFromCurrent: true,
        },
      ],
    },
    {
      title: t('tag.editor.section_style'),
      fields: [
        {
          type: 'color',
          name: 'color',
          label: t('tag.editor.color'),
          help: t('tag.editor.color_help'),
          suggestions: 5,
        },
        {
          type: 'component',
          name: 'preview',
          component: () => import('@admin/Modules/Tag/TagPreviewField.vue'),
        },
      ],
    },
    {
      title: t('tag.editor.section_status'),
      fields: [
        { type: 'switch', name: 'disabled', label: t('tag.editor.disabled'), help: t('tag.editor.disabled_help') },
      ],
    },
  ],
  meta: ['createdAt', 'updatedAt', 'author', 'pageCount'],
  update: { operation: 'admin_tag_patch' },
  delete: { operation: 'admin_tag_delete' },
  messages: {
    created: t('tag.notify.created'),
    saved: t('tag.notify.saved'),
    confirmDelete: t('tag.dialog.confirm_delete'),
    deleted: t('tag.notify.deleted'),
  },
  rowLabel: (tag) => tag.label || tag.translations[0]?.label || '',
});
