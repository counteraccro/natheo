import { buildUiLabels, UI_LABELS } from '@/vue/Composables/Admin/Ui/useUiLabels';

/** Libellés de test : la clé admin_ui sans son domaine, suivie de ses paramètres, pour des assertions lisibles */
export const labels = buildUiLabels((fullKey, params) => {
  const key = fullKey.replace(/^admin_ui\./, '');
  return params ? `${key}(${Object.values(params).join(',')})` : key;
});

/** Options de montage qui fournissent les libellés */
export const withLabels = (options: Record<string, unknown> = {}) => ({
  ...options,
  global: { provide: { [UI_LABELS as symbol]: labels }, ...((options.global as object) ?? {}) },
});
