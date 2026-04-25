export interface Locales {
  locales: string[];
  localesTranslate: Record<string, string>;
  current: string;
}

export interface MenuFormTranslate {
  title_edit: string;
  title_new: string;
  input_search_page: string;
  input_search_page_placeholder: string;
  block_fr: string;
  block_es: string;
  block_en: string;
  input_link_text: string;
  input_link_url: string;
  input_link_external_url: string;
  radio_label_url_interne: string;
  radio_label_url_externe: string;
  title_position: string;
  parent_label: string;
  parent_empty: string;
  url_type_label: string;
  position_column_label: string;
  position_row_label: string;
  radio_label_disabled_element: string;
  radio_label_enabled_element: string;
  text_info_disabled_element: string;
  text_info_enabled_element: string;
  element_link_target_label: string;
  element_link_target_label_self: string;
  element_link_target_label_blank: string;
}

export interface MenuTreeTranslate {
  btn_new_menu_element: string;
  tag_page: string;
  tag_link: string;
}

export interface Translate {
  toast_title_success: string;
  toast_title_error: string;
  toast_time: string;
  select_locale: string;
  select_type: string;
  title_demo: string;
  title_demo_warning: string;
  title_architecture: string;
  title_global_form: string;
  error_empty_value: string;
  msg_wait_loading: string;
  btn_save: string;
  btn_new: string;
  no_select_menu_form: string;
  no_select_menu_form_msg: string;
  no_select_menu_form_msg_2: string;
  no_select_menu_form_msg_3: string;
  no_select_menu_form_msg_4: string;
  no_select_menu_form_msg_5: string;
  no_select_menu_form_msg_6: string;
  help_title: string;
  help_edition: string;
  help_delete: string;
  help_new: string;
  help_disabled: string;
  title_generic_data: string;
  input_name_label: string;
  input_name_placeholder: string;
  input_name_error: string;
  select_position_label: string;
  select_type_label: string;
  checkbox_disabled_label: string;
  checkbox_enabled_label: string;
  checkbox_disabled_label_msg: string;
  checkbox_enabled_label_msg: string;
  checkbox_default_menu_true_label: string;
  checkbox_default_menu_false_label: string;
  checkbox_default_menu_true_label_msg: string;
  checkbox_default_menu_false_label_msg: string;
  error_no_element: string;
  menu_element_confirm_delete_title: string;
  menu_element_confirm_delete_body: string;
  menu_element_confirm_delete_btn_ok: string;
  menu_element_confirm_delete_btn_ko: string;
  btn_new_menu_element: string;
  msg_no_element_new_menu: string;
  select_page_label: string;
  select_page_no_page: string;
  select_page_info: string;
  menu_form: MenuFormTranslate;
  menu_tree: MenuTreeTranslate;
}

export interface Urls {
  load_menu: string;
  save_menu: string;
  delete_menu_element: string;
  new_menu_element: string;
  update_parent_menu_element: string;
  list_parent_menu_element: string;
  reorder_menu_element: string;
}

/** Entrée générique pour les selects (list_position, list_type, etc.) */
export interface MenuDatas {
  list_position: Record<string, string>;
  list_type: Record<string, Record<string, string>>;
}

export interface MenuElementTranslation {
  id: number;
  locale: string;
  textLink: string;
  externalLink: string;
  link: string;
}

export interface MenuElement {
  id: number;
  columnPosition: number;
  rowPosition: number;
  linkTarget: '_self' | '_blank';
  disabled: boolean;
  parent: number | '';
  page: number;
  menuElementTranslations: MenuElementTranslation[];
  children?: MenuElement[];
}

export interface Menu {
  id: number;
  name: string;
  type: number;
  position: number;
  renderOrder: number;
  defaultMenu: boolean;
  disabled: boolean;
  pageMenu: number[];
  menuElements: MenuElement[];
}

export interface PageTranslation {
  title: string;
  url: string;
}

export type PageLocales = Record<string, PageTranslation>;

export interface LoadMenuData {
  all_elements: MenuElement[];
  name: string;
  logo: string | null;
  url_site: string;
  pages: Record<string, PageLocales>;
}

export interface LoadMenuResponse {
  menu: Menu;
  data: LoadMenuData;
}
