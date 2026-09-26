import type { ListDumpTranslations } from '@/ts/DatabaseManager/ListDump.type';

export interface DatabaseManagerUrls {
  load_schema_database: string;
  load_schema_table: string;
  load_tables_database: string;
  save_database: string;
  all_dump_file: string;
  delete_dump_file: string;
}

export interface DatabaseManagerCsrfTokens {
  save_database: string;
  delete_dump_file: string;
}

export interface DatabaseManagerDumpOptionTranslations {
  title: string;
  sub_title_1: string;
  sub_title_2: string;
  select_all: string;
  filename_label: string;
  filename_help: string;
  select_tables: string;
  option_data_label: string;
  option_data: string;
  option_table: string;
  option_table_data: string;
  help_title: string;
  help_body: string;
  warning_title: string;
  warning_body: string;
  btn_generate: string;
}

export interface DatabaseManagerTranslations {
  loading: string;
  btn_generate_dump: string;
  btn_schema_bdd: string;
  btn_schema_table: string;
  btn_liste_dump: string;
  stat_nb_table: string;
  stat_nb_row: string;
  stat_size: string;
  action: string;
  error_generic: string;
  dump_option: DatabaseManagerDumpOptionTranslations;
  list_dump: ListDumpTranslations;
}

export type DatabaseManagerDumpDataType = 'table' | 'data' | 'data_table';

export interface DatabaseManagerDumpOptions {
  filename: string;
  /** valeur des boutons radio : '1' toutes les tables, '0' uniquement la sélection */
  all: '1' | '0';
  tables: string[];
  data: DatabaseManagerDumpDataType;
}

export interface DatabaseManagerTable {
  name: string;
  columns: string[];
}

export interface DatabaseManagerResponse {
  success: boolean;
  msg: string;
}
