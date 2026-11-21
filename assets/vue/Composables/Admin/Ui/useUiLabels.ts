/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Libellés internes des composants du back-office : construits depuis le domaine admin_ui, fournis une fois par l'application
 */
import { inject, provide, type InjectionKey } from 'vue';
import type { UiLabels, UiTranslator } from '@/ts/Admin/Ui/UiLabels.type';

const UI_LABELS: InjectionKey<UiLabels> = Symbol('UiLabels');

/**
 * Construit les libellés depuis un traducteur du domaine admin_ui
 * @param t traduit une clé complète du domaine admin_ui
 */
export function buildUiLabels(t: UiTranslator): UiLabels {
  return {
    dialog: { close: t('admin_ui.dialog.close') },
    confirmDialog: { cancel: t('admin_ui.confirm_dialog.cancel'), confirm: t('admin_ui.confirm_dialog.confirm') },
    alert: { close: t('admin_ui.alert.close') },
    toast: { close: t('admin_ui.toast.close'), region: t('admin_ui.toast.region') },
    button: { loading: t('admin_ui.button.loading') },
    field: { required: t('admin_ui.field.required') },
    errorSummary: {
      title: (count) => t('admin_ui.error_summary.title', { count }),
      item: (label, message) => t('admin_ui.error_summary.item', { label, message }),
    },
    tabs: { hasErrors: t('admin_ui.tabs.has_errors') },
    input: { counter: (count, max) => t('admin_ui.input.counter', { count, max }) },
    colorInput: {
      picker: t('admin_ui.color_input.picker'),
      hex: t('admin_ui.color_input.hex'),
      suggestions: t('admin_ui.color_input.suggestions'),
      more: t('admin_ui.color_input.more'),
      use: (color: string) => t('admin_ui.color_input.use', { color }),
    },
    pagination: {
      nav: t('admin_ui.pagination.nav'),
      previous: t('admin_ui.pagination.previous'),
      next: t('admin_ui.pagination.next'),
      page: (page) => t('admin_ui.pagination.page', { page }),
      itemsPerPage: t('admin_ui.pagination.items_per_page'),
      range: (from, to, total) => t('admin_ui.pagination.range', { from, to, total }),
    },
    dataTable: {
      actions: t('admin_ui.data_table.actions'),
      moreActions: t('admin_ui.data_table.more_actions'),
      options: t('admin_ui.data_table.options'),
      columns: t('admin_ui.data_table.columns'),
      searchPlaceholder: t('admin_ui.data_table.search_placeholder'),
      searchLabel: t('admin_ui.data_table.search_label'),
      searchLocal: (count, total) => t('admin_ui.data_table.search_local', { count, total }),
      searchHint: t('admin_ui.data_table.search_hint'),
      searchPending: t('admin_ui.data_table.search_pending'),
      searchRemote: (count) => t('admin_ui.data_table.search_remote', { count }),
      searchBack: t('admin_ui.data_table.search_back'),
      emptyTitle: t('admin_ui.data_table.empty_title'),
      emptyText: t('admin_ui.data_table.empty_text'),
      selectRow: (label) => t('admin_ui.data_table.select_row', { label }),
      selectPage: t('admin_ui.data_table.select_page'),
      selected: (count) => t('admin_ui.data_table.selected', { count }),
      selectAll: (total) => t('admin_ui.data_table.select_all', { total }),
      clearSelection: t('admin_ui.data_table.clear_selection'),
      selectionRegion: t('admin_ui.data_table.selection_region'),
      sqlShow: t('admin_ui.data_table.sql_show'),
      sqlTitle: t('admin_ui.data_table.sql_title'),
      sqlSave: t('admin_ui.data_table.sql_save'),
      sqlCopy: t('admin_ui.data_table.sql_copy'),
      sqlCopied: t('admin_ui.data_table.sql_copied'),
      sqlHide: t('admin_ui.data_table.sql_hide'),
    },
    shell: { skipToContent: t('admin_ui.shell.skip_to_content') },
    sidebar: {
      nav: t('admin_ui.sidebar.nav'),
      close: t('admin_ui.sidebar.close'),
      version: (version) => t('admin_ui.sidebar.version', { version }),
      userMenu: t('admin_ui.sidebar.user_menu'),
    },
    header: {
      openMenu: t('admin_ui.header.open_menu'),
      searchLabel: t('admin_ui.header.search_label'),
      searchPlaceholder: t('admin_ui.header.search_placeholder'),
      new: t('admin_ui.header.new'),
      appearance: t('admin_ui.header.appearance'),
      mode: t('admin_ui.header.mode'),
      modeLight: t('admin_ui.header.mode_light'),
      modeDark: t('admin_ui.header.mode_dark'),
      color: t('admin_ui.header.color'),
      themes: {
        violet: t('admin_ui.header.theme_violet'),
        blue: t('admin_ui.header.theme_blue'),
        green: t('admin_ui.header.theme_green'),
        red: t('admin_ui.header.theme_red'),
      },
      notifications: t('admin_ui.header.notifications'),
      impersonation: (name) => t('admin_ui.header.impersonation', { name }),
      impersonationExit: t('admin_ui.header.impersonation_exit'),
    },
    breadcrumb: { nav: t('admin_ui.breadcrumb.nav') },
    debugBar: {
      open: t('admin_ui.debug_bar.open'),
      close: t('admin_ui.debug_bar.close'),
      loginAs: t('admin_ui.debug_bar.login_as'),
      currentUser: t('admin_ui.debug_bar.current_user'),
    },
  };
}

/**
 * Fournit les libellés aux composants descendants
 */
export function provideUiLabels(labels: UiLabels): void {
  provide(UI_LABELS, labels);
}

/**
 * Libellés fournis par un composant parent ; erreur explicite sans fournisseur plutôt qu'un texte en dur
 */
export function useUiLabels(): UiLabels {
  const labels = inject(UI_LABELS, null);
  if (labels === null) {
    throw new Error('UiLabels absents : appeler provideUiLabels() dans un composant parent.');
  }
  return labels;
}

/** Clé d'injection, pour fournir les libellés hors d'un setup (tests, app.provide) */
export { UI_LABELS };
