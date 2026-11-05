/**
 * Types pour le composant NotificationList
 * @author Gourdon Aymeric
 * @version 1.0
 */
import type { NotificationItemTranslate } from '@/ts/Notification/Notification.type';

export interface NotificationListUrls {
  list: string;
  statistics: string;
  purge: string;
  update: string;
  readAll: string;
  delete: string;
}

/** Jetons CSRF des actions d'écriture sur les notifications */
export interface NotificationListCsrfTokens {
  update: string;
  delete: string;
  purge: string;
  readAll: string;
}

export interface NotificationListTranslate {
  loading: string;
  empty: string;
  onlyNotRead: string;
  readAll: string;
  read: string;
  noRead: string;
  deleteSelected: string;
  deleteAll: string;
  all: string;
  allSuccess: string;
  statNonLu: string;
  statToday: string;
  statAll: string;
  confirmTitle: string;
  confirmBtnOK: string;
  confirmBtnNo: string;
  confirmMsg: string;
  confirmSingleDelete: string;
  notification: NotificationItemTranslate;
}

export interface NotificationCategory {
  id: string;
  name: string;
}

/** Notification cochée, indexée par son identifiant */
export interface NotificationChecked {
  id: number | string;
  isRead: boolean;
}
