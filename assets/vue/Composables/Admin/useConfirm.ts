/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Confirmation d'une action par une promesse, affichée par le UiConfirmDialog unique du socle
 */
import { shallowRef } from 'vue';
import type { DialogTone } from '@/ts/Admin/Ui/Overlay/UiConfirmDialog.type';
import type { IconName } from '@/ts/Global/Ui/UiIcon.type';

export interface ConfirmRequest {
  title: string;
  message?: string;
  tone?: DialogTone;
  icon?: IconName;
  confirmLabel?: string;
  confirmIcon?: IconName;
}

interface PendingConfirm extends ConfirmRequest {
  resolve: (confirmed: boolean) => void;
}

/** Demande affichée, null quand aucune confirmation n'est ouverte */
const current = shallowRef<PendingConfirm | null>(null);

/**
 * Ouvre la confirmation ; une demande encore ouverte est refusée
 * @param request
 * @return true si l'utilisateur confirme
 */
function confirm(request: ConfirmRequest): Promise<boolean> {
  current.value?.resolve(false);
  return new Promise<boolean>((resolve) => {
    current.value = { ...request, resolve };
  });
}

/**
 * Répond à la demande ouverte, appelé par le dialogue
 * @param confirmed
 */
function answer(confirmed: boolean): void {
  const pending = current.value;
  current.value = null;
  pending?.resolve(confirmed);
}

export function useConfirm() {
  return { confirm, answer, current };
}
