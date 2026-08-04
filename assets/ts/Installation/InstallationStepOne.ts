export interface InstallationStepOneUrls {
  update_env: string;
  check_database: string;
  update_app_secret: string;
  create_bdd: string;
  create_schema: string;
  step_2: string;
}

export interface InstallationStepOneTranslate {
  loading: string;
  title: string;
  title_thx_h1: string;
  description_thx_1: string;
  title_h1: string;
  description_1: string;
  description_2: string;
  config_bdd_title: string;
  config_bdd_input_type_label: string;
  config_bdd_input_login_label: string;
  config_bdd_input_password_label: string;
  config_bdd_input_ip_label: string;
  config_bdd_input_port_label: string;
  config_bdd_btn_test_config: string;
  config_bdd_btn_test_config_loading: string;
  config_bdd_loading_msg_test_connexion: string;
  config_bdd_loading_msg_test_connexion_success: string;
  config_bdd_loading_msg_test_connexion_ko: string;
  config_bdd_loading_msg_update_file: string;
  create_bdd_h1: string;
  create_bdd_description_1: string;
  create_bdd_description_2: string;
  create_bdd_alert_title: string;
  create_bdd_alert_text_1: string;
  create_bdd_alert_text_2: string;
  create_bdd_alert_text_3: string;
  create_bdd_alert_text_4: string;
  create_bdd_alert_text_5: string;
  create_bdd_title: string;
  create_bdd_input_bdd_name_label: string;
  create_bdd_input_bdd_name_error: string;
  create_bdd_input_version_label: string;
  create_bdd_input_version_error: string;
  create_bdd_input_charset_label: string;
  create_bdd_btn_create: string;
  create_bdd_loading_msg_update_file: string;
  create_bdd_loading_msg_update_secret: string;
  create_bdd_loading_msg_update_secret_success: string;
  create_bdd_loading_msg_update_secret_ko: string;
  create_bdd_loading_msg_create_bdd: string;
  create_bdd_loading_msg_create_bdd_success: string;
  create_bdd_loading_msg_create_bdd_ko: string;
  create_bdd_loading_msg_create_table: string;
  create_bdd_loading_msg_create_table_success: string;
  create_bdd_loading_msg_create_table_ko: string;
  create_bdd_loading_msg_success: string;
}

export interface InstallationStepOneLocales {
  [key: string]: string;
}

export interface InstallationStepOneDatas {
  [key: string]: unknown;
}

export interface CheckDatabaseResponse {
  connexion: boolean;
}
