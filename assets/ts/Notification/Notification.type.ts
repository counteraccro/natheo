/**
 * Types pour les composants Notification et NotificationList
 * @author Gourdon Aymeric
 * @version 1.0
 */

export interface NotificationItem {
  id: number;
  title: string;
  content: string;
  read: boolean;
  category: string;
  tmpObjectId: string | null;
  createdAt: { timestamp: number };
}

export interface NotificationItemTranslate {
  seeComment: string;
  seePage: string;
  read: string;
  time: string;
}
