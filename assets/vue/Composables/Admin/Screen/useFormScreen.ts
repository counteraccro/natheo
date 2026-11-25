/**
 * @author Gourdon Aymeric
 * @version 1.3
 * État et actions d'un écran de formulaire décrit par des données : lecture, champs par langue (recopie de la
 * langue de l'interface, cochée par défaut à la création), enregistrement, suppression, retour à la liste et
 * suivi des écritures faites ailleurs
 */
import { computed, onScopeDispose, ref } from 'vue';
import { useRouter, type Router } from 'vue-router';
import { notify } from '@/vue/Composables/Global/toast';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { useConfirm, type ConfirmRequest } from '@/vue/Composables/Admin/useConfirm';
import { useAdminResource, type AdminResource } from '@/vue/Composables/Admin/useAdminResource';
import { leaveGuard } from '@/vue/Composables/Admin/leaveGuard';
import {
  changeConcerns,
  notifyResourceChange,
  onResourceChange,
  type ResourceChange,
} from '@/vue/Composables/Admin/Screen/resourceChanges';
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

  const copyFields = descriptor.sections
    .flatMap((section) => section.fields)
    .filter((field): field is TranslationsField => field.type === 'translations' && field.copyFromCurrent === true);
  const copying = ref(isNew.value && copyFields.length > 0);
  const copyToOthers = (field: TranslationsField, value: string): void =>
    locales.filter((other) => other !== currentLocale).forEach((other) => write(field, other, value));

  const sections = computed(() =>
    descriptor.sections.filter((section) => section.on === undefined || (section.on === 'create') === isNew.value)
  );

  /** Données envoyées : champs des sections affichées, lignes par langue dans l'ordre des langues du site */
  const payload = (): Row => {
    const body: Row = {};
    sections.value
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

  const canEdit = computed(() => isNew.value || can.can('edit', descriptor.update.operation, resource.data.value));
  const mode = computed(() => (isNew.value ? 'create' : canEdit.value ? 'edit' : 'view'));

  return {
    descriptor,
    resource,
    isNew,
    title: computed(() =>
      isNew.value ? t(descriptor.title.create) : t(descriptor.title[mode.value], { id: String(resource.id.value) })
    ),
    description: computed(() => {
      const key = descriptor.description?.[mode.value];
      return key ? t(key) : '';
    }),
    sections,
    locales,
    currentLocale,
    activeLocale: ref(currentLocale),
    canEdit,
    canDelete: computed(
      () =>
        !isNew.value &&
        descriptor.delete !== undefined &&
        can.can('delete', descriptor.delete.operation, resource.data.value)
    ),
    dirty: computed(() => leaveGuard.isDirty(descriptor.leaveKey)),
    translation,
    copying,
    setCopying(value: boolean): void {
      copying.value = value;
      if (value) copyFields.forEach((field) => copyToOthers(field, translation(field, currentLocale)));
    },
    isCopied: (field: TranslationsField, locale: string): boolean =>
      copying.value && field.copyFromCurrent === true && locale !== currentLocale,
    setTranslation(field: TranslationsField, locale: string, value: string): void {
      if (copying.value && field.copyFromCurrent && locale === currentLocale) copyToOthers(field, value);
      write(field, locale, value);
    },
    translationPath(field: TranslationsField, locale: string): string {
      return `${field.name}[${locales.indexOf(locale)}].${field.field}`;
    },
    async save(): Promise<void> {
      const created = isNew.value;
      const saved = await resource.save(payload());
      if (saved === null) return;
      notifyResourceChange(descriptor.resource, { ids: [saved.id], source: resource });
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
      const id = resource.id.value;
      if (confirmed && id !== null && (await resource.remove())) {
        notifyResourceChange(descriptor.resource, { ids: [id], deleted: true, source: resource });
        notify('success', t(descriptor.messages.deleted, { label: deleted }));
        await router.push({ name: descriptor.back });
      }
    },
    reload: () => resource.load(),
    async externalChange(change: ResourceChange): Promise<void> {
      if (change.source === resource || isNew.value || !changeConcerns(change, resource.id.value)) return;
      if (change.deleted) {
        resource.dispose();
        await router.push({ name: descriptor.back });
      } else if (leaveGuard.isDirty(descriptor.leaveKey)) {
        resource.conflict.value = true;
      } else {
        await resource.load();
      }
    },
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
  // Une action de la liste affichée à côté a modifié ou supprimé l'élément
  onScopeDispose(onResourceChange(descriptor.resource, (change) => void screen.externalChange(change)));
  void resource.load();
  return screen;
}
