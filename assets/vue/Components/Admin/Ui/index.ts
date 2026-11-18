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
