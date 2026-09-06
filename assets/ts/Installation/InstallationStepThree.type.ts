export interface InstallationStepThreeUrls {
  create_user: string;
  change_env: string;
  load_fixtures: string;
  clear_cache: string;
  step_4: string;
}

export interface InstallationStepThreeTranslate {
  loading: string;
  title: string;
  title_h1: string;
  fondateur_description: string;
  fondateur_titre_card: string;
  fondateur_login_label: string;
  fondateur_login_placeholder: string;
  fondateur_login_error: string;
  fondateur_email_label: string;
  fondateur_email_placeholder: string;
  fondateur_email_error: string;
  fondateur_password_label: string;
  fondateur_password_error: string;
  fondateur_password_weak: string;
  fondateur_password_normal: string;
  fondateur_password_strong: string;
  fondateur_password_help: string;
  fondateur_btn_create: string;
  fondateur_loading_msg: string;
  fondateur_success: string;
  debug_titre: string;
  debug_texte_1: string;
  debug_texte_2: string;
  debug_texte_3: string;
  debug_texte_4: string;
  debug_texte_5: string;
  debug_texte_6: string;
  finish_installation_title: string;
  finish_installation_text_1: string;
  finish_installation_text_2: string;
  finish_installation_btn: string;
  finish_installation_loading_msg_success: string;
  finish_installation_loading_msg_config: string;
  finish_installation_loading_msg_config_success: string;
  finish_installation_loading_msg_config_ko: string;
  finish_installation_loading_msg_installation_data: string;
  finish_installation_loading_msg_installation_data_success: string;
  finish_installation_loading_msg_installation_data_ko: string;
  finish_installation_loading_msg_cache: string;
  finish_installation_loading_msg_cache_success: string;
  finish_installation_loading_msg_cache_ko: string;
}

export interface InstallationStepThreeLocales {
  [key: string]: string;
}

export interface InstallationStepThreeDatas {
  debug_mode: boolean;
}

export interface UserPayload {
  login: string;
  email: string;
  password: string;
}

export interface CreateUserResponse {
  success: boolean;
  error?: string;
}

export interface ApiActionResponse {
  success: boolean;
  error?: string;
}

export type PasswordStrength = 'weak' | 'normal' | 'strong';

export type ValidableField = 'login' | 'email' | 'password';

export type FieldValidation = Record<ValidableField, boolean | null>;

export type FinishStepKey = 'config' | 'installData' | 'cache';

export interface FinishStepResult {
  success: boolean;
  message: string;
}

export type FinishStepStatus = Record<FinishStepKey, FinishStepResult>;
