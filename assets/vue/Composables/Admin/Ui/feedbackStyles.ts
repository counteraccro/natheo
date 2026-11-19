/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Icône et couleurs de chaque variante d'alerte et de notification
 */
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';
import type { NotificationType } from '@/ts/Toast/Toast.type';

export const FEEDBACK_ICONS: Record<NotificationType, IconName> = {
  success: 'check-circle',
  info: 'info',
  warning: 'exclamation-triangle',
  danger: 'exclamation-circle',
};

/** Couleur du texte de la variante */
export const FEEDBACK_TEXT: Record<NotificationType, string> = {
  success: 'text-success',
  info: 'text-info',
  warning: 'text-warning',
  danger: 'text-danger',
};

/** Fond teinté de la variante */
export const FEEDBACK_SOFT: Record<NotificationType, string> = {
  success: 'bg-success-soft',
  info: 'bg-info-soft',
  warning: 'bg-warning-soft',
  danger: 'bg-danger-soft',
};

/** Bordure à 30 % de la couleur de la variante */
export const FEEDBACK_BORDER: Record<NotificationType, string> = {
  success: 'border-[color-mix(in_srgb,var(--success)_30%,transparent)]',
  info: 'border-[color-mix(in_srgb,var(--info)_30%,transparent)]',
  warning: 'border-[color-mix(in_srgb,var(--warning)_30%,transparent)]',
  danger: 'border-[color-mix(in_srgb,var(--danger)_30%,transparent)]',
};

/** Rôle ARIA : les avertissements et erreurs interrompent le lecteur d'écran */
export const feedbackRole = (type: NotificationType): 'alert' | 'status' =>
  type === 'warning' || type === 'danger' ? 'alert' : 'status';
