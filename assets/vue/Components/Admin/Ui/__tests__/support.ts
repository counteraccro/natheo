import { buildUiLabels, UI_LABELS } from '@/vue/Composables/Admin/Ui/useUiLabels';

/** Libellés de test : la clé admin_ui suivie de ses paramètres, pour des assertions lisibles */
export const labels = buildUiLabels((key, params) =>
  params ? `${key}(${Object.values(params).join(',')})` : key
);

/** Options de montage qui fournissent les libellés */
export const withLabels = (options: Record<string, unknown> = {}) => ({
  ...options,
  global: { provide: { [UI_LABELS as symbol]: labels }, ...((options.global as object) ?? {}) },
});
