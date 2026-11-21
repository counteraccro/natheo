/**
 * @author Gourdon Aymeric
 * @version 1.0
 * État et actions d'un écran de formulaire décrit par des données : lecture, champs par langue (recopie de la
 * langue de l'interface à la création), enregistrement, suppression et retour à la liste
 */
import { computed } from 'vue';
import { useRouter, type Router } from 'vue-router';
import { notify } from '@/vue/Composables/Global/toast';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { useConfirm, type ConfirmRequest } from '@/vue/Composables/Admin/useConfirm';
import { useAdminResource, type AdminResource } from '@/vue/Composables/Admin/useAdminResource';
import type { AdminCan } from '@/vue/Composables/Admin/useCan';
import type { Translator } from '@/vue/Composables/Admin/useTranslate';
import type { FormScreenDescriptor, FormScreenState, TranslationsField } from '@/ts/Admin/Screen/FormScreen.type';
import type { ScreenItem } from '@/ts/Admin/Screen/Screen.type';

type Row = Record<string, unknown>;

export interface FormScreenDeps<T> {
  t: Translator['t'];
  can: AdminCan;
  confirm: (request: ConfirmRequest) => Promise<boolean>;
  router: Pick<Router, 'push' | 'replace'>;
  resource: AdminResource<T>;
  locales: string[];
  currentLocale: string;
}

/**
 * Crée l'état de l'écran ; la lecture est lancée par l'appelant
 * @param descriptor
 * @param deps
 */
export function createFormScreen<T extends ScreenItem>(
  descriptor: FormScreenDescriptor<T>,
  deps: FormScreenDeps<T>
): FormScreenState<T> {
  const { t, can, confirm, router, resource, locales, currentLocale } = deps;
  const data = (): Row => (resource.data.value ?? {}) as Row;
  const isNew = computed(() => resource.id.value === null);
  const rowsOf = (field: TranslationsField): Row[] => (data()[field.name] as Row[] | undefined) ?? [];
  const translation = (field: TranslationsField, locale: string): string =>
    String(rowsOf(field).find((row) => row.locale === locale)?.[field.field] ?? '');

  const write = (field: TranslationsField, locale: string, value: string): void => {
    const rows = rowsOf(field);
    const row = rows.find((candidate) => candidate.locale === locale);
    if (row) row[field.field] = value;
    else data()[field.name] = [...rows, { locale, [field.field]: value }];
  };

  /** Données envoyées : champs déclarés, lignes par langue dans l'ordre des langues du site */
  const payload = (): Row => {
    const body: Row = {};
    descriptor.sections
      .flatMap((section) => section.fields)
      .forEach((field) => {
        if (field.type === 'component') return;
        body[field.name] =
          field.type === 'translations'
            ? locales.map((locale) => ({ locale, [field.field]: translation(field, locale) }))
            : data()[field.name];
      });
    return body;
  };

  const label = (): string => (resource.data.value ? descriptor.rowLabel(resource.data.value) : '');

  return {
    descriptor,
    resource,
    isNew,
    title: computed(() =>
      isNew.value ? t(descriptor.title.create) : t(descriptor.title.edit, { id: String(resource.id.value) })
    ),
    locales,
    currentLocale,
    canEdit: computed(() => isNew.value || can.can('edit', descriptor.update.operation, resource.data.value)),
    canDelete: computed(
      () =>
        !isNew.value &&
        descriptor.delete !== undefined &&
        can.can('delete', descriptor.delete.operation, resource.data.value)
    ),
    translation,
    setTranslation(field: TranslationsField, locale: string, value: string): void {
      if (isNew.value && field.copyFromCurrent && locale === currentLocale) {
        const previous = translation(field, locale);
        locales
          .filter((other) => other !== locale && [previous, ''].includes(translation(field, other)))
          .forEach((other) => write(field, other, value));
      }
      write(field, locale, value);
    },
    translationPath(field: TranslationsField, locale: string): string {
      return `${field.name}[${locales.indexOf(locale)}].${field.field}`;
    },
    async save(): Promise<void> {
      const created = isNew.value;
      const saved = await resource.save(payload());
      if (saved === null) return;
      notify('success', t(created ? descriptor.messages.created : descriptor.messages.saved, { label: label() }));
      if (created) {
        await router.replace({ name: descriptor.editRoute, params: { id: String(saved.id) } });
      }
    },
    async remove(): Promise<void> {
      const confirmed = await confirm({
        title: t(descriptor.messages.confirmDelete, { label: label() }),
        message: t('admin_app.screen.confirm_irreversible'),
        tone: 'danger',
        icon: 'trash',
        confirmLabel: t('admin_app.screen.action_delete'),
        confirmIcon: 'trash',
      });
      const deleted = label();
      if (confirmed && (await resource.remove())) {
        notify('success', t(descriptor.messages.deleted, { label: deleted }));
        await router.push({ name: descriptor.back });
      }
    },
    reload: () => resource.load(),
    back(): void {
      void router.push({ name: descriptor.back });
    },
  };
}

/**
 * État d'un écran de formulaire, lecture lancée
 * @param descriptor
 * @param id identifiant, null pour une création
 */
export function useFormScreen<T extends ScreenItem>(
  descriptor: FormScreenDescriptor<T>,
  id: string | number | null
): FormScreenState<T> {
  const { t, can, bootstrap } = useAdminContext();
  const resource = useAdminResource<T>(descriptor.resource, id, {
    initial: () => descriptor.initial() as T,
    leaveKey: descriptor.leaveKey,
  });
  const screen = createFormScreen(descriptor, {
    t,
    can,
    confirm: useConfirm().confirm,
    router: useRouter(),
    resource,
    locales: bootstrap.locales,
    currentLocale: bootstrap.locale,
  });
  void resource.load();
  return screen;
}
