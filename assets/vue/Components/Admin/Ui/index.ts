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
