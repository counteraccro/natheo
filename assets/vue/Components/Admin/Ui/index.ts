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
export { default as UiButton } from './Form/UiButton.vue';
export { default as UiField } from './Form/UiField.vue';
export { default as UiInput } from './Form/UiInput.vue';
export { default as UiTextarea } from './Form/UiTextarea.vue';
export { default as UiSelect } from './Form/UiSelect.vue';
export { default as UiCheckbox } from './Form/UiCheckbox.vue';
export { default as UiSwitch } from './Form/UiSwitch.vue';
export { default as UiColorInput } from './Form/UiColorInput.vue';
export type { ButtonVariant, ButtonSize } from '@/ts/Admin/Ui/Form/UiButton.type';
export type { SelectOption } from '@/ts/Admin/Ui/Form/UiSelect.type';
export { default as UiDialog } from './Overlay/UiDialog.vue';
export { default as UiConfirmDialog } from './Overlay/UiConfirmDialog.vue';
export { default as UiDropdown } from './Overlay/UiDropdown.vue';
export { default as UiDropdownItem } from './Overlay/UiDropdownItem.vue';
export { default as UiDropdownLabel } from './Overlay/UiDropdownLabel.vue';
export { default as UiTooltip } from './Overlay/UiTooltip.vue';
export { useFloating, computePosition } from '@/vue/Composables/Admin/Ui/useFloating';
export type { DialogTone, DialogSize } from '@/ts/Admin/Ui/Overlay/UiConfirmDialog.type';
export type { DropdownItemType } from '@/ts/Admin/Ui/Overlay/UiDropdown.type';
export type { FloatingPlacement, FloatingPosition } from '@/ts/Admin/Ui/Overlay/UiFloating.type';
export { default as UiTabs } from './Display/UiTabs.vue';
export { default as UiBadge } from './Display/UiBadge.vue';
export { default as UiAlert } from './Feedback/UiAlert.vue';
export { default as UiToastRegion } from './Feedback/UiToastRegion.vue';
export { default as UiEmptyState } from './Feedback/UiEmptyState.vue';
export { default as UiSkeleton } from './Feedback/UiSkeleton.vue';
export { notify, dismissNotification } from '@/vue/Composables/Global/toast';
export type { TabItem } from '@/ts/Admin/Ui/Display/UiTabs.type';
export type { BadgeTone } from '@/ts/Admin/Ui/Display/UiBadge.type';
export type { AlertVariant } from '@/ts/Admin/Ui/Feedback/UiAlert.type';
export type { NotificationItem, NotificationType } from '@/ts/Toast/Toast.type';
export { default as UiDropdownSeparator } from './Overlay/UiDropdownSeparator.vue';
export { default as UiPagination } from './Data/UiPagination.vue';
export { default as UiDataTable } from './Data/UiDataTable.vue';
export type {
  DataTableAction,
  DataTableColumn,
  DataTableRow,
  DataTableSort,
  DataTableSearchMode,
} from '@/ts/Admin/Ui/Data/UiDataTable.type';
