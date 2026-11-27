/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Écrans du module Commentaires : liste avec modération groupée et formulaire de modération
 */
import {
  defineCollectionScreen,
  defineFormScreen,
  translationKey as t,
} from '@/vue/Composables/Admin/Screen/defineScreen';
import { COMMENT_STATUSES, MODERATION_COMMENT_MAX_LENGTH } from '@/vue/Composables/Admin/Comment/commentStatus';
import type { AdminComment } from '@/ts/Admin/Api/AdminComment.type';

const RESOURCE = '/admin/comments';
const PATCH = 'admin_comment_patch';
const statusOptions = COMMENT_STATUSES.map((status) => ({ value: status.value, label: status.label }));

export const commentCollection = defineCollectionScreen<AdminComment>({
  resource: RESOURCE,
  title: t('comment.collection.title'),
  description: t('comment.collection.description'),
  icon: 'message',
  read: { operation: 'admin_comment_get_collection' },
  header: () => import('@admin/Modules/Comment/CommentStatusBanner.vue'),
  columns: [
    { key: 'id', label: t('comment.collection.column_id'), sortable: true },
    { key: 'excerpt', label: t('comment.collection.column_excerpt') },
    { key: 'author', label: t('comment.collection.column_author'), sortable: true },
    {
      key: 'page',
      label: t('comment.collection.column_page'),
      sortable: true,
      hideBelow: 'md',
      component: () => import('@admin/Modules/Comment/CommentPageLink.vue'),
    },
    {
      key: 'status',
      label: t('comment.collection.column_status'),
      sortable: true,
      component: () => import('@admin/Modules/Comment/CommentStatusBadge.vue'),
    },
    {
      key: 'createdAt',
      label: t('comment.collection.column_created_at'),
      sortable: true,
      type: 'date',
      hideBelow: 'lg',
    },
  ],
  filters: [
    {
      type: 'select',
      param: 'moderation',
      label: t('comment.collection.filter_moderation'),
      allLabel: t('comment.collection.filter_moderation_all'),
      options: statusOptions,
    },
    'author',
    { type: 'query', param: 'pageId' },
  ],
  actions: [{ type: 'edit', route: 'comment_edit', operation: PATCH }],
  bulkActions: [
    {
      type: 'patch',
      id: 'validate',
      label: t('comment.collection.bulk_validate'),
      icon: 'check-circle',
      operation: PATCH,
      payload: { status: 'validate' },
      eligible: (comment) => comment.status !== 'validate',
    },
    {
      type: 'patch',
      id: 'wait_validation',
      label: t('comment.collection.bulk_wait_validation'),
      icon: 'hourglass',
      operation: PATCH,
      payload: { status: 'wait_validation' },
      eligible: (comment) => comment.status !== 'wait_validation',
    },
    {
      type: 'patch',
      id: 'moderate',
      label: t('comment.collection.bulk_moderate'),
      icon: 'ban',
      operation: PATCH,
      payload: { status: 'moderate' },
      dialog: () => import('@admin/Modules/Comment/CommentModerateDialog.vue'),
    },
  ],
  rowLabel: (comment) => comment.author,
  messages: { authorMe: t('comment.collection.filter_author_me') },
});

export const commentForm = defineFormScreen<AdminComment>({
  resource: RESOURCE,
  title: {
    create: t('comment.editor.title_edit'),
    edit: t('comment.editor.title_edit'),
    view: t('comment.editor.title_view'),
  },
  description: {
    create: t('comment.editor.description_edit'),
    edit: t('comment.editor.description_edit'),
    view: t('comment.editor.description_view'),
  },
  icon: 'message',
  back: 'comment_index',
  leaveKey: 'comment',
  initial: () => ({}),
  sections: [
    {
      title: t('comment.editor.section_comment'),
      description: t('comment.editor.section_comment_description'),
      kind: 'content',
      fields: [{ type: 'textEditor', name: 'comment', label: t('comment.editor.comment'), preset: 'basic', rows: 6 }],
    },
    {
      title: t('comment.editor.section_moderation'),
      description: t('comment.editor.section_moderation_description'),
      kind: 'status',
      fields: [
        { type: 'select', name: 'status', label: t('comment.editor.status'), options: statusOptions },
        {
          type: 'component',
          name: 'statusNote',
          component: () => import('@admin/Modules/Comment/CommentStatusNote.vue'),
        },
        {
          type: 'textarea',
          name: 'moderationComment',
          label: t('comment.editor.moderation_comment'),
          help: t('comment.editor.moderation_comment_help'),
          maxlength: MODERATION_COMMENT_MAX_LENGTH,
          visibleWhen: (data) => data.status === 'moderate',
        },
      ],
    },
  ],
  meta: [
    { name: 'author', label: t('comment.editor.meta_author'), value: (comment) => comment.author },
    {
      name: 'email',
      label: t('comment.editor.meta_email'),
      value: (comment) => comment.email,
      href: (comment) => (comment.email ? `mailto:${comment.email}` : null),
    },
    {
      name: 'page',
      label: t('comment.editor.meta_page'),
      value: (comment) => comment.page?.title ?? null,
      href: (comment) => comment.page?.url ?? null,
    },
    'createdAt',
    'updatedAt',
    { name: 'ip', label: t('comment.editor.meta_ip'), value: (comment) => comment.ip },
    { name: 'userAgent', label: t('comment.editor.meta_user_agent'), value: (comment) => comment.userAgent },
  ],
  read: { operation: 'admin_comment_get' },
  update: { operation: PATCH },
  messages: { saved: t('comment.notify.saved') },
  rowLabel: (comment) => comment.author,
});
