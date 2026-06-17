export interface PageContentFormTranslate {
  title: string;
  input_url_label: string;
  input_url_info: string;
  input_titre_label: string;
  input_titre_info: string;
  list_render_label: string;
  list_render_help: string;
  list_categories_label: string;
  list_categories_help: string;
  header_img_title: string;
  header_img_help: string;
  header_img_no_img: string;
  header_img_remove: string;
  header_img_change: string;
  mediatheque: Record<string, string>;
}

export interface PageContentTranslate {
  title: string;
  page_content_block: Record<string, string>;
}

export interface PageHistoryTranslate {
  title: string;
  description: string;
  empty: string;
  id: string;
  time: string;
  user: string;
  action: string;
  reload: string;
}

export interface PageSaveTranslate {
  title: string;
  list_status_label: string;
  list_status_help: string;
  list_menu_label: string;
  list_menu_help: string;
  list_menu_empty: string;
  list_menu_disabled: string;
  list_landing_page_label: string;
  select_page_landing_page: string;
  select_page_normal_page: string;
  list_landing_page_help: string;
  btn_save: string;
  btn_see_ext: string;
}

export interface AutoCompleteTranslate {
  auto_complete_label: string;
  auto_complete_placeholder: string;
  auto_complete_help: string;
  auto_complete_btn: string;
  auto_complete_btn_loading: string;
  auto_complete_empty_result: string;
}

export interface PageCommentTranslate {
  title: string;
  input_open_comment: string;
  input_status_comment_label: string;
  input_status_comment_help: string;
  info: string;
  comment_open: string;
  comment_close: string;
  comment_moderate: string;
}

export interface PageSeoTranslate {
  title: string;
  help_legend: string;
  help_description: string;
  input_meta_description_label: string;
  input_meta_description_help: string;
  input_meta_keywords_label: string;
  input_meta_keywords_help: string;
  input_meta_author_label: string;
  input_meta_author_help: string;
  input_meta_copyright_label: string;
  input_meta_copyright_help: string;
}

export interface PageTranslations {
  select_locale: string;
  onglet_content: string;
  onglet_seo: string;
  onglet_tags: string;
  onglet_comments: string;
  onglet_history: string;
  onglet_save: string;
  loading: string;
  msg_auto_save_success: string;
  msg_add_tag_success: string;
  msg_del_tag_success: string;
  msg_remove_content_success: string;
  msg_add_content_success: string;
  msg_error_url_no_unique: string;
  msg_titre_restore_history: string;
  msg_btn_restore_history: string;
  msg_btn_cancel_restore_history: string;
  toast_title_success: string;
  toast_title_error: string;
  toast_title_auto_save: string;
  toast_time: string;
  tag_title: string;
  tag_sub_title: string;
  page_no_exist_title: string;
  page_no_exist_text: string;
  btn_back: string;
  btn_new: string;
  page_content_form: PageContentFormTranslate;
  page_content: PageContentTranslate;
  page_history: PageHistoryTranslate;
  page_save: PageSaveTranslate;
  auto_complete: AutoCompleteTranslate;
  page_comment: PageCommentTranslate;
  page_seo: PageSeoTranslate;
}

export interface Urls {
  load_page: string;
  load_tab_history: string;
  auto_save: string;
  reload_page_history: string;
  auto_complete_tag: string;
  tag_by_name: string;
  save: string;
  new_content: string;
  liste_content_by_id: string;
  is_unique_url_page: string;
  info_render_block: string;
  page_preview: string;
  load_media: string;
  listing: string;
  new_page: string;
}

export interface Locales {
  locales: string[];
  localesTranslate: Record<string, string>;
  current: string;
}

export interface PageOptionsCommentaire {
  open: string;
  new_comment: string;
}

export interface PageData {
  list_status: Record<string, string>;
  list_render: Record<string, string>;
  list_content: Record<string, string>;
  list_categories: Record<string, string>;
  list_comments_status: Record<string, string>;
  url_front: string;
  options_commentaire: PageOptionsCommentaire;
}
