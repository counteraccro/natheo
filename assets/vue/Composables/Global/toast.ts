/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Toasts de l'admin et de l'installation : état partagé par tous les îlots Vue de la page
 * (même instance de module), affiché par l'unique ToastContainer monté dans la base Twig ; notifications du nouveau
 * back-office dans une file à part, affichée par UiToastRegion
 */
import { reactive } from 'vue';
import axios from 'axios';
import { uiTrans } from '@/vue/Composables/Global/uiTranslate';
import type { NotificationItem, NotificationType, ToastItem, ToastType } from '@/ts/Toast/Toast.type';

const DURATION = 3000;
let lastId = 0;

export const toastState = reactive<{ items: ToastItem[] }>({ items: [] });

/**
 * Ferme un toast
 * @param id identifiant du toast
 */
export const closeToast = (id: number): void => {
  const index = toastState.items.findIndex((item) => item.id === id);
  if (index !== -1) {
    toastState.items.splice(index, 1);
  }
};

/**
 * Ajoute un toast, fermé automatiquement après DURATION
 * @param type type du toast
 * @param msg message (HTML autorisé, messages construits côté serveur)
 */
const pushToast = (type: ToastType, msg: string): void => {
  if (!msg) {
    return;
  }
  const id = ++lastId;
  toastState.items.push({ id, type, msg });
  setTimeout(() => closeToast(id), DURATION);
};

/**
 * Affiche un toast de succès
 * @param msg message
 */
export const toastSuccess = (msg: string): void => pushToast('success', msg);

/**
 * Affiche un toast d'erreur
 * @param msg message
 */
export const toastError = (msg: string): void => pushToast('danger', msg);

/**
 * Affiche l'erreur d'une requête AJAX : message renvoyé par le serveur, sinon message générique.
 * Les requêtes annulées sont ignorées ; l'erreur est toujours journalisée en console
 * @param error erreur reçue dans le catch
 */
export const toastRequestError = (error: unknown): void => {
  console.error(error);
  if (axios.isCancel(error)) {
    return;
  }
  const msg = axios.isAxiosError(error) ? error.response?.data?.msg : undefined;
  toastError(typeof msg === 'string' && msg !== '' ? msg : uiTrans('error'));
};

const NOTIFICATION_DURATION = 5000;
let lastNotificationId = 0;

export const notificationState = reactive<{ items: NotificationItem[] }>({ items: [] });

/**
 * Ajoute une notification du nouveau back-office, la plus récente en tête
 * @param type variante
 * @param title titre
 * @param text texte facultatif
 * @param duration durée d'affichage
 * @returns identifiant de la notification
 */
export const notify = (
  type: NotificationType,
  title: string,
  text?: string,
  duration: number = NOTIFICATION_DURATION
): number => {
  const id = ++lastNotificationId;
  notificationState.items.unshift({ id, type, title, text, duration });
  return id;
};

/**
 * Retire une notification
 * @param id identifiant de la notification
 */
export const dismissNotification = (id: number): void => {
  const index = notificationState.items.findIndex((item) => item.id === id);
  if (index !== -1) {
    notificationState.items.splice(index, 1);
  }
};
