/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Informations d'un élément affichées par les écrans de formulaire : auteur, pages, dates et informations du module
 */
import { computed, type ComputedRef } from 'vue';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { formatDateTime, formatRelative } from '@/vue/Composables/Admin/Screen/screenFormat';
import type { FormScreenState } from '@/ts/Admin/Screen/FormScreen.type';
import type { ScreenAuthor, ScreenItem } from '@/ts/Admin/Screen/Screen.type';

export interface FormMetaItem {
  name: string;
  label: string;
  value: string;
  /** Date complète au survol */
  title?: string;
  /** Lien de la valeur */
  href?: string;
}

/**
 * Informations déclarées par meta dans la description du formulaire
 * @param screen
 */
export function useFormMeta<T extends ScreenItem>(screen: FormScreenState<T>): ComputedRef<FormMetaItem[]> {
  const { t, bootstrap } = useAdminContext();
  const descriptor = screen.descriptor;
  const row = computed(() => (screen.resource.data.value ?? {}) as Record<string, unknown>);
  return computed(() => {
    const item = row.value;
    return (descriptor.meta ?? []).map((name): FormMetaItem => {
      if (typeof name === 'object') {
        const element = screen.resource.data.value as T;
        const value = name.value(element);
        return {
          name: name.name,
          label: t(name.label),
          value: name.date ? formatRelative(value, bootstrap.locale) : value || '—',
          title: name.date ? formatDateTime(value, bootstrap.locale) : undefined,
          href: (value && name.href?.(element)) || undefined,
        };
      }
      if (name === 'author') {
        const author = item.author as ScreenAuthor | null | undefined;
        return {
          name,
          label: t('admin_app.screen.meta_author'),
          value: author?.login ?? t('admin_app.screen.meta_author_none'),
        };
      }
      if (name === 'pageCount') {
        return { name, label: t('admin_app.screen.meta_page_count'), value: String(item.pageCount ?? 0) };
      }
      const iso = item[name] as string | null | undefined;
      return {
        name,
        label: name === 'createdAt' ? t('admin_app.screen.meta_created_at') : t('admin_app.screen.meta_updated_at'),
        value: formatRelative(iso, bootstrap.locale),
        title: formatDateTime(iso, bootstrap.locale),
      };
    });
  });
}
