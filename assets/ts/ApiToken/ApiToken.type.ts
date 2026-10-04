/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types pour le composant ApiToken (création / édition d'un token API)
 */

/**
 * Token formaté par ApiTokenService::getApiTokenFormData()
 */
export interface ApiToken {
  id: number | null;
  name: string | null;
  comment: string | null;
  roles: string[];
  disabled: boolean;
  // Format Y-m-d, compatible avec un input de type date
  expiresAt: string | null;
  lastUsedAt: string | null;
}

/**
 * Urls générées par ApiTokenController::add()
 */
export interface ApiTokenUrls {
  regenerate_token: string;
  save_api_token: string;
  index_api_token: string;
  delete_api_token: string;
}

export interface ApiTokenDatas {
  roles: Record<string, string>;
}

export interface ApiTokenCsrfTokens {
  save: string;
  regenerate: string;
  delete: string;
}

/**
 * Traductions renvoyées par ApiTokenTranslate
 */
export interface ApiTokenTranslate {
  loading: string;
  title_add: string;
  title_edit: string;
  description_add: string;
  description_edit: string;
  btn_regenerate_token: string;
  btn_back_list: string;
  btn_copy_past: string;
  title_label: string;
  name_error: string;
  title_help: string;
  title_placeholder: string;
  comment_label: string;
  comment_help: string;
  comment_placeholder: string;
  token_label: string;
  token_copy_success: string;
  input_token_help_add: string;
  token_once_warning: string;
  token_hidden: string;
  expires_at_label: string;
  expires_at_help: string;
  last_used_at_label: string;
  last_used_at_never: string;
  select_label_role: string;
  help_role: string;
  help_role_read: string;
  help_role_write: string;
  help_role_admin: string;
  btn_edit_token_api: string;
  btn_cancel_edit_token: string;
  btn_delete_token: string;
  btn_save_token_api: string;
  modale_title_confirm_edit: string;
  modale_title_confirm_text: string;
  modale_title_confirm_btn_ok: string;
  modale_title_confirm_btn_ko: string;
  modale_title_confirm_delete: string;
  modale_title_confirm_delete_text: string;
  modale_title_confirm_regenerate: string;
  modale_title_confirm_regenerate_text: string;
}

export interface ApiTokenFieldValidation {
  isValide: boolean;
  msg: string;
}

export interface ApiTokenValidation {
  name: ApiTokenFieldValidation;
}

/**
 * Réponse ajax standard (suppression)
 */
export interface ApiTokenAjaxResponse {
  success: boolean;
  msg: string;
}

/**
 * Réponse de la sauvegarde ou de la régénération, token en clair si généré
 */
export interface ApiTokenSaveResponse extends ApiTokenAjaxResponse {
  token: string;
}
