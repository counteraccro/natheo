/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Statuts de modération d'un commentaire : libellé, ton et icône
 */
import { translationKey as t } from '@/vue/Composables/Admin/Screen/defineScreen';
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';
import type { BadgeTone } from '@/ts/Admin/Ui/Display/UiBadge.type';
import type { CommentStatusSlug } from '@/ts/Admin/Api/AdminComment.type';

export interface CommentStatusDefinition {
  value: CommentStatusSlug;
  /** Clé de traduction */
  label: string;
  tone: BadgeTone;
  icon: IconName;
}

/** Statuts dans l'ordre de CommentStatus */
export const COMMENT_STATUSES: CommentStatusDefinition[] = [
  { value: 'wait_validation', label: t('comment.state.wait_validation'), tone: 'warning', icon: 'hourglass' },
  { value: 'validate', label: t('comment.state.validate'), tone: 'success', icon: 'check-circle' },
  { value: 'moderate', label: t('comment.state.moderate'), tone: 'danger', icon: 'ban' },
];

/**
 * Statut d'un slug, celui en attente pour un slug inconnu
 * @param slug
 */
export function commentStatus(slug: string): CommentStatusDefinition {
  return COMMENT_STATUSES.find((status) => status.value === slug) ?? COMMENT_STATUSES[0];
}
