/**
 * Types pour le composant MyAccountDangerZone
 * @author Gourdon Aymeric
 * @version 1.0
 */

export interface MyAccountDangerZoneTranslate {
  btn_delete: string;
  btn_disabled: string;
  btn_replace: string;
  delete_btn_cancel: string;
  delete_btn_confirm: string;
  delete_content_1: string;
  delete_content_2: string;
  delete_description: string;
  delete_title: string;
  disabled_btn_cancel: string;
  disabled_btn_confirm: string;
  disabled_content_1: string;
  disabled_content_2: string;
  disabled_description: string;
  disabled_title: string;
  loading: string;
  replace_btn_cancel: string;
  replace_btn_confirm: string;
  replace_content_1: string;
  replace_content_2: string;
  replace_description: string;
  replace_title: string;
}

/** Jetons CSRF des actions de la danger zone */
export interface MyAccountDangerZoneCsrfTokens {
  disabled: string;
  delete: string;
}

export type MyAccountDangerZoneModal = 'disabled' | 'delete';
