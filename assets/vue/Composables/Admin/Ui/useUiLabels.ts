/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Libellés internes des composants du back-office : construits depuis le domaine admin_ui, fournis une fois par l'application
 */
import { inject, provide, type InjectionKey } from 'vue';
import type { UiLabels, UiTranslator } from '@/ts/Admin/Ui/UiLabels.type';

const UI_LABELS: InjectionKey<UiLabels> = Symbol('UiLabels');

/**
 * Construit les libellés depuis un traducteur du domaine admin_ui
 * @param t traduit une clé sans le préfixe admin_ui
 */
export function buildUiLabels(t: UiTranslator): UiLabels {
  return {
    dialog: { close: t('dialog.close') },
    confirmDialog: { cancel: t('confirm_dialog.cancel'), confirm: t('confirm_dialog.confirm') },
    alert: { close: t('alert.close') },
    toast: { close: t('toast.close'), region: t('toast.region') },
    button: { loading: t('button.loading') },
    field: { required: t('field.required') },
    errorSummary: { title: (count) => t('error_summary.title', { count }) },
    tabs: { hasErrors: t('tabs.has_errors') },
    input: { counter: (count, max) => t('input.counter', { count, max }) },
    colorInput: { picker: t('color_input.picker'), hex: t('color_input.hex') },
    pagination: {
      nav: t('pagination.nav'),
      previous: t('pagination.previous'),
      next: t('pagination.next'),
      page: (page) => t('pagination.page', { page }),
      itemsPerPage: t('pagination.items_per_page'),
      range: (from, to, total) => t('pagination.range', { from, to, total }),
    },
    dataTable: {
      actions: t('data_table.actions'),
      moreActions: t('data_table.more_actions'),
      options: t('data_table.options'),
      columns: t('data_table.columns'),
      searchPlaceholder: t('data_table.search_placeholder'),
      searchLabel: t('data_table.search_label'),
      searchClear: t('data_table.search_clear'),
      searchLocal: (count, total) => t('data_table.search_local', { count, total }),
      searchHint: t('data_table.search_hint'),
      searchPending: t('data_table.search_pending'),
      searchRemote: (count) => t('data_table.search_remote', { count }),
      searchBack: t('data_table.search_back'),
      emptyTitle: t('data_table.empty_title'),
      emptyText: t('data_table.empty_text'),
      selectRow: (label) => t('data_table.select_row', { label }),
      selectPage: t('data_table.select_page'),
      selected: (count) => t('data_table.selected', { count }),
      selectAll: (total) => t('data_table.select_all', { total }),
      clearSelection: t('data_table.clear_selection'),
      selectionRegion: t('data_table.selection_region'),
      sqlShow: t('data_table.sql_show'),
      sqlTitle: t('data_table.sql_title'),
      sqlSave: t('data_table.sql_save'),
      sqlCopy: t('data_table.sql_copy'),
      sqlHide: t('data_table.sql_hide'),
    },
    shell: { skipToContent: t('shell.skip_to_content') },
    sidebar: {
      nav: t('sidebar.nav'),
      close: t('sidebar.close'),
      version: (version) => t('sidebar.version', { version }),
      userMenu: t('sidebar.user_menu'),
    },
    header: {
      openMenu: t('header.open_menu'),
      searchLabel: t('header.search_label'),
      searchPlaceholder: t('header.search_placeholder'),
      new: t('header.new'),
      appearance: t('header.appearance'),
      mode: t('header.mode'),
      modeLight: t('header.mode_light'),
      modeDark: t('header.mode_dark'),
      color: t('header.color'),
      themes: {
        violet: t('header.theme_violet'),
        blue: t('header.theme_blue'),
        green: t('header.theme_green'),
        red: t('header.theme_red'),
      },
      notifications: t('header.notifications'),
      impersonation: (name) => t('header.impersonation', { name }),
      impersonationExit: t('header.impersonation_exit'),
    },
    breadcrumb: { nav: t('breadcrumb.nav') },
    debugBar: {
      open: t('debug_bar.open'),
      close: t('debug_bar.close'),
      loginAs: t('debug_bar.login_as'),
      currentUser: t('debug_bar.current_user'),
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
