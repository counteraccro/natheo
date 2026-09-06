/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types pour le composant BlockHelpFirstConnexion
 */

export interface BlockHelpFirstConnexionUrls {
  load_block_dashboard: string;
  update_user_data: string;
}

export interface BlockHelpFirstConnexionTranslate {
  title: string;
  loading: string;
  sub_title: string;
  text_1: string;
  text_end_success: string;
  text_end: string;
  btn_def_hide: string;
  modal_confirm_title: string;
  modal_confirm_body_1: string;
  modal_confirm_body_2: string;
  modal_confirm_btn_ok: string;
  modal_confirm_btn_ko: string;
  msg_hide_success: string;
}

export interface BlockHelpFirstConnexionDatas {
  help_first_connexion: boolean;
  user_data_key_first_connexion: string;
}

export interface ConfigCheckItem {
  success: boolean;
  msg: string | string[];
  msgTitle?: string;
}

export interface ConfigLink {
  link: string;
  label: string;
}

export interface ConfigLinks {
  link_options: ConfigLink;
  link_tokens: ConfigLink;
}

export interface LoadBlockDashboardResponse {
  success: boolean;
  error: string | null;
  body: ConfigCheckItem[];
  configComplete: boolean;
  links: ConfigLinks;
}

export interface UpdateUserDataResponse {
  success: boolean;
  msg?: string;
}
