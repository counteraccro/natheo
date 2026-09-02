export interface SqlManagerQuery {
  id: number | null;
  name: string | null;
  query: string | null;
}

export interface DatabaseTable {
  name: string;
  columns: string[];
}

export interface SqlManagerUrls {
  load_sql_manager: string;
  load_data_database: string;
  execute_sql: string;
  save: string;
  index: string;
  new: string;
}

export interface SqlManagerTranslations {
  label_list_field: string;
  label_list_field_2: string;
  toast_msg_exec_success: string;
  toast_msg_exec_error: string;
  no_query_manager_title: string;
  no_query_manager_text: string;
  btn_back: string;
  btn_new: string;
  title_my_query: string;
  sub_title_my_query: string;
  btn_execute_query: string;
  btn_save_query: string;
  label_name: string;
  label_name_placeholder: string;
  error_name_empty: string;
  label_textarea_query: string;
  error_query_empty: string;
  help_text_1: string;
  bloc_query: string;
  bloc_query_sub_title: string;
  label_list_table: string;
  placeholder_table: string;
  btn_add_table: string;
  help_select_table: string;
  placeholder_field: string;
  help_select_field: string;
  bloc_result: string;
  bloc_result_sub_title: string;
  no_result_query: string;
  no_result_query_help: string;
}

export interface ToastState {
  show: boolean;
  msg: string;
}

export interface LoadSqlManagerResponse {
  sqlManager: SqlManagerQuery;
}

export interface LoadDataDatabaseResponse {
  dataInfo: DatabaseTable[];
}

export interface ExecuteSqlResponse {
  data: {
    error: string;
    result: Record<string, string | number | null>[];
    header: string[];
  };
}

export interface SaveResponse {
  success: boolean;
  msg: string;
  redirect?: boolean;
  url_redirect?: string;
}
