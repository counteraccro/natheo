export type ToastType = 'success' | 'danger';

export type ToastItem = {
  id: number;
  type: ToastType;
  msg: string;
};

/** Notification du nouveau back-office, affichée par UiToastRegion */
export type NotificationType = 'success' | 'info' | 'warning' | 'danger';

export type NotificationItem = {
  id: number;
  type: NotificationType;
  title: string;
  text?: string;
  /** Durée d'affichage en millisecondes, décomptée seulement quand la notification est visible */
  duration: number;
};
