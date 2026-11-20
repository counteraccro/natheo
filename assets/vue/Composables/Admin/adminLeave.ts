/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Confirmation avant de quitter un formulaire modifié de l'application : navigation interne, liens qui rechargent et
 * fermeture de l'onglet
 */
import { clickedLink } from '@/vue/Composables/Admin/clickedLink';
import { leaveGuard } from '@/vue/Composables/Admin/leaveGuard';
import type { ConfirmRequest } from '@/vue/Composables/Admin/useConfirm';

export interface AdminLeave {
  /** true si rien n'est modifié ou si l'utilisateur accepte de perdre ses modifications */
  confirmLeave(): Promise<boolean>;
  /** Clic sur un lien qui recharge la page */
  onClick(event: MouseEvent): void;
  onBeforeUnload(event: BeforeUnloadEvent): void;
}

export interface AdminLeaveOptions {
  /** Option OS_CONFIRM_LEAVE_FORM */
  enabled: boolean;
  confirm(request: ConfirmRequest): Promise<boolean>;
  /** Texte de la confirmation */
  request(): ConfirmRequest;
  /** Ouverture d'une adresse, remplacée dans les tests */
  assign?: (url: string) => void;
}

/**
 * Crée la confirmation de départ
 * @param options
 */
export function createAdminLeave(options: AdminLeaveOptions): AdminLeave {
  const assign = options.assign ?? ((url: string) => window.location.assign(url));
  // Départ accepté : la fermeture de la page ne redemande rien
  let leaving = false;

  const confirmLeave = async (): Promise<boolean> =>
    !options.enabled || !leaveGuard.isDirty() || options.confirm(options.request());

  return {
    confirmLeave,
    onClick(event: MouseEvent): void {
      const link = options.enabled && leaveGuard.isDirty() ? clickedLink(event) : null;
      if (link === null) return;
      event.preventDefault();
      void confirmLeave().then((confirmed) => {
        if (confirmed) {
          leaving = true;
          assign(link.href);
        }
      });
    },
    onBeforeUnload(event: BeforeUnloadEvent): void {
      if (options.enabled && !leaving && leaveGuard.isDirty()) {
        event.preventDefault();
      }
    },
  };
}
