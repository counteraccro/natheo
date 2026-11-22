/**
 * @author Gourdon Aymeric
 * @version 1.0
 * API publique du design system du back-office (alias @ui) : composants et types stables pour les modules et les bundles
 */
export { default as UiIcon } from '@/vue/Components/Global/Ui/UiIcon.vue';
export { default as BrandLogo } from '@/vue/Components/Global/Ui/BrandLogo.vue';
export type { IconName, IconDefinition } from '@/ts/Global/Ui/UiIcon.type';
export { buildUiLabels, provideUiLabels, useUiLabels, UI_LABELS } from '@/vue/Composables/Admin/Ui/useUiLabels';
export type { UiLabels, UiTranslator } from '@/ts/Admin/Ui/UiLabels.type';
export { default as UiButton } from '@admin/Ui/Form/UiButton.vue';
export { default as UiField } from '@admin/Ui/Form/UiField.vue';
export { default as UiInput } from '@admin/Ui/Form/UiInput.vue';
export { default as UiTextarea } from '@admin/Ui/Form/UiTextarea.vue';
export { default as UiSelect } from '@admin/Ui/Form/UiSelect.vue';
export { default as UiCheckbox } from '@admin/Ui/Form/UiCheckbox.vue';
export { default as UiSwitch } from '@admin/Ui/Form/UiSwitch.vue';
export { default as UiColorInput } from '@admin/Ui/Form/UiColorInput.vue';
export type { ButtonVariant, ButtonSize } from '@/ts/Admin/Ui/Form/UiButton.type';
export type { SelectOption } from '@/ts/Admin/Ui/Form/UiSelect.type';
export { default as UiDialog } from '@admin/Ui/Overlay/UiDialog.vue';
export { default as UiConfirmDialog } from '@admin/Ui/Overlay/UiConfirmDialog.vue';
export { default as UiDropdown } from '@admin/Ui/Overlay/UiDropdown.vue';
export { default as UiDropdownItem } from '@admin/Ui/Overlay/UiDropdownItem.vue';
export { default as UiDropdownLabel } from '@admin/Ui/Overlay/UiDropdownLabel.vue';
export { default as UiTooltip } from '@admin/Ui/Overlay/UiTooltip.vue';
export { useFloating, computePosition } from '@/vue/Composables/Admin/Ui/useFloating';
export type { DialogTone, DialogSize } from '@/ts/Admin/Ui/Overlay/UiConfirmDialog.type';
export type { DropdownItemType } from '@/ts/Admin/Ui/Overlay/UiDropdown.type';
export type { FloatingPlacement, FloatingPosition } from '@/ts/Admin/Ui/Overlay/UiFloating.type';
export { default as UiTabs } from '@admin/Ui/Display/UiTabs.vue';
export { default as UiBadge } from '@admin/Ui/Display/UiBadge.vue';
export { default as UiAlert } from '@admin/Ui/Feedback/UiAlert.vue';
export { default as UiToastRegion } from '@admin/Ui/Feedback/UiToastRegion.vue';
export { default as UiEmptyState } from '@admin/Ui/Feedback/UiEmptyState.vue';
export { default as UiSkeleton } from '@admin/Ui/Feedback/UiSkeleton.vue';
export { notify, dismissNotification } from '@/vue/Composables/Global/toast';
export type { TabItem } from '@/ts/Admin/Ui/Display/UiTabs.type';
export type { BadgeTone } from '@/ts/Admin/Ui/Display/UiBadge.type';
export type { AlertVariant } from '@/ts/Admin/Ui/Feedback/UiAlert.type';
export type { NotificationItem, NotificationType } from '@/ts/Toast/Toast.type';
export { default as UiDropdownSeparator } from '@admin/Ui/Overlay/UiDropdownSeparator.vue';
export { default as UiPagination } from '@admin/Ui/Data/UiPagination.vue';
export { default as UiSqlBlock } from '@admin/Ui/Data/UiSqlBlock.vue';
export { default as UiDataTable } from '@admin/Ui/Data/UiDataTable.vue';
export type {
  DataTableAction,
  DataTableColumn,
  DataTableRow,
  DataTableSort,
  DataTableSearchMode,
} from '@/ts/Admin/Ui/Data/UiDataTable.type';
export { useTableSearch } from '@/vue/Composables/Admin/Ui/useTableSearch';
export { randomHexColor } from '@/vue/Composables/Admin/Ui/randomHexColor';
export type { TableSearch, TableSearchOptions } from '@/vue/Composables/Admin/Ui/useTableSearch';
export { useAppearance, setMode, setTheme } from '@/vue/Composables/Admin/Ui/useAppearance';
export type { ColorMode, ThemeColor } from '@/ts/Admin/Layout/AdminHeader.type';
export { default as UiForm } from '@admin/Ui/Form/UiForm.vue';
export { default as UiErrorSummary } from '@admin/Ui/Form/UiErrorSummary.vue';
export { useFormErrors, violationsFrom, fieldIdOf } from '@/vue/Composables/Admin/Ui/useFormErrors';
export type { ApiViolation, FormErrors, FormErrorItem } from '@/ts/Admin/Ui/Form/UiForm.type';
export { default as AdminShell } from '@admin/Layout/AdminShell.vue';
export { default as AdminSidebar } from '@admin/Layout/AdminSidebar.vue';
export { default as AdminHeader } from '@admin/Layout/AdminHeader.vue';
export { default as AdminBreadcrumb } from '@admin/Layout/AdminBreadcrumb.vue';
export { default as AdminPageHeader } from '@admin/Layout/AdminPageHeader.vue';
export { default as AdminDebugBar } from '@admin/Layout/AdminDebugBar.vue';
export { default as AdminAppearanceMenu } from '@admin/Layout/AdminAppearanceMenu.vue';
export type { SidebarItem, SidebarUser } from '@/ts/Admin/Layout/AdminSidebar.type';
export type { HeaderSection, HeaderShortcut, LayoutChoice } from '@/ts/Admin/Layout/AdminHeader.type';
export type { BreadcrumbItem } from '@/ts/Admin/Layout/AdminBreadcrumb.type';
export type { DebugSection, DebugUser } from '@/ts/Admin/Layout/AdminDebugBar.type';
