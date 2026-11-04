/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Toasts de l'admin et de l'installation : état partagé par tous les îlots Vue de la page
 * (même instance de module), affiché par l'unique ToastContainer monté dans la base Twig
 */
import { reactive } from 'vue';
import axios from 'axios';
import { uiTrans } from '@/vue/Composables/Global/uiTranslate';
import type { ToastItem, ToastType } from '@/ts/Toast/Toast.type';

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
