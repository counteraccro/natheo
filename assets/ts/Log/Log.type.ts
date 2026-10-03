/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types pour le composant Log (lecture des fichiers de logs)
 */
import type { GenericGridResponse } from '@/ts/Grid/GenericGrid.type';

/**
 * Filtre de temporalité de la liste des fichiers
 */
export type LogTimeFilter = 'all' | 'now' | 'yesterday';

/**
 * Fichier de log, path est le chemin relatif au dossier des logs (ex : cms/prod/auth-2026-01-01.log)
 */
export interface LogFile {
  type: 'file';
  name: string;
  path: string;
}

/**
 * Traductions générées par LogTranslate::getTranslate()
 */
export interface LogTranslate {
  log_block_search_title: string;
  log_block_search_sub_title: string;
  log_select_file_label: string;
  log_select_file: string;
  log_select_time_label: string;
  log_select_time_all: string;
  log_select_time_now: string;
  log_select_time_yesterday: string;
  log_file: string;
  log_file_size: string;
  log_file_ligne: string;
  log_btn_delete_file: string;
  log_btn_download_file: string;
  log_empty_file: string;
  log_delete_file_confirm: string;
  log_delete_file_confirm_2: string;
  log_delete_file_loading: string;
  log_delete_file_btn_close: string;
  log_btn_reload: string;
  log_btn_delete_ok: string;
  log_btn_delete_ko: string;
  toast_title_success: string;
  toast_time: string;
  toast_title_error: string;
  log_error_request: string;
}

export interface LogFilesResponse {
  success: true;
  files: LogFile[];
}

/**
 * Grid retourné par LoggerService::loadLogFile()
 */
export interface LogGridResponse extends GenericGridResponse {
  taille: string;
}

export interface LogLoadFileResponse {
  success: true;
  msg: string;
  grid: LogGridResponse;
}

/**
 * Réponse de la suppression, ou de n'importe quelle route en erreur
 */
export interface LogActionResponse {
  success: boolean;
  msg: string;
}
