/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Toasts de l'admin et de l'installation : état partagé par tous les îlots Vue de la page
 * (même instance de module), affiché par l'unique ToastContainer monté dans la base Twig
 */
import { reactive } from 'vue';
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
