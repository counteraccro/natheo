export interface AdvancedOptionsUrls {
  switch_env: string;
  reset_data: string;
  reset_database: string;
}

export interface AdvancedOptionsCsrfTokens {
  switch_env: string;
  reset_data: string;
  reset_database: string;
}

export interface AdvancedOptionsData {
  app_debug: boolean;
  app_env: string;
}

export interface AdvancedOptionsBlocTranslations {
  title: string;
  text_1: string;
  warning: string;
  btn: string;
}

export interface AdvancedOptionsModaleConfirmTranslations {
  modale_title: string;
  modale_body_text_1: string;
  modale_body_text_2: string;
  modale_btn_confirm: string;
  modale_btn_undo: string;
}

export interface AdvancedOptionsMsgInfoTranslations {
  title: string;
  switch_env: string;
  switch_env_end: string;
  reset_data: string;
  reset_data_end: string;
  reset_database: string;
  reset_database_end: string;
}

export interface AdvancedOptionsTranslations {
  toast_title_success: string;
  toast_title_error: string;
  toast_time: string;
  switch_env_title: string;
  switch_env_subtitle_prod: string;
  switch_env_subtitle_dev: string;
  switch_env_define_dev: string;
  switch_env_define_dev_1: string;
  switch_env_define_dev_2: string;
  switch_env_define_dev_3: string;
  switch_env_define_dev_4: string;
  switch_env_define_dev_warning: string;
  switch_env_define_prod: string;
  switch_env_define_prod_1: string;
  switch_env_define_prod_2: string;
  switch_env_define_prod_3: string;
  switch_env_define_prod_warning: string;
  switch_env_btn_dev: string;
  switch_env_btn_prod: string;
  title_danger_zone: string;
  subtitle_danger_zone: string;
  warning_danger_zone: string;
  error_generic: string;
  confirm_modale_env: AdvancedOptionsModaleConfirmTranslations;
  confirm_modale_reset_database: AdvancedOptionsModaleConfirmTranslations;
  confirm_modale_reset_data: AdvancedOptionsModaleConfirmTranslations;
  msg_info: AdvancedOptionsMsgInfoTranslations;
  reload_data: AdvancedOptionsBlocTranslations;
  reset_database: AdvancedOptionsBlocTranslations;
}

export type AdvancedOptionsConfirmAction = 'switchEnv' | 'resetDatabase' | 'resetData';

export interface AdvancedOptionsModalConfirm {
  title: string;
  text_1: string;
  text_2: string;
  btn_undo: string;
  btn_go: string;
}

export interface AdvancedOptionsResponse {
  success: boolean;
  msg: string;
  redirect?: string;
}
