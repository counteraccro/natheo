/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Libellés internes des composants du back-office (domaine admin_ui), messages à paramètres déjà formatés
 */

/** Traduit une clé du domaine admin_ui (sans le préfixe) avec ses paramètres ICU */
export type UiTranslator = (key: string, params?: Record<string, string | number>) => string;

export interface UiLabels {
  dialog: { close: string };
  confirmDialog: { cancel: string; confirm: string };
  alert: { close: string };
  toast: { close: string; region: string };
  button: { loading: string };
  field: { required: string };
  errorSummary: { title: (count: number) => string };
  tabs: { hasErrors: string };
  input: { counter: (count: number, max: number) => string };
  colorInput: { picker: string; hex: string };
  pagination: {
    nav: string;
    previous: string;
    next: string;
    page: (page: number) => string;
    itemsPerPage: string;
    range: (from: number, to: number, total: number) => string;
  };
  dataTable: {
    actions: string;
    moreActions: string;
    options: string;
    columns: string;
    searchPlaceholder: string;
    searchLabel: string;
    searchClear: string;
    searchLocal: (count: number, total: number) => string;
    searchHint: string;
    searchPending: string;
    searchRemote: (count: number) => string;
    searchBack: string;
    emptyTitle: string;
    emptyText: string;
    selectRow: (label: string) => string;
    selectPage: string;
    selected: (count: number) => string;
    selectAll: (total: number) => string;
    clearSelection: string;
    selectionRegion: string;
    sqlShow: string;
    sqlTitle: string;
    sqlSave: string;
    sqlCopy: string;
    sqlHide: string;
  };
  shell: { skipToContent: string };
  sidebar: { nav: string; close: string; version: (version: string) => string; userMenu: string };
  header: {
    openMenu: string;
    searchLabel: string;
    searchPlaceholder: string;
    new: string;
    appearance: string;
    mode: string;
    modeLight: string;
    modeDark: string;
    color: string;
    themes: Record<'violet' | 'blue' | 'green' | 'red', string>;
    notifications: string;
    impersonation: (name: string) => string;
    impersonationExit: string;
  };
  breadcrumb: { nav: string };
  debugBar: { open: string; close: string; loginAs: string; currentUser: string };
}
