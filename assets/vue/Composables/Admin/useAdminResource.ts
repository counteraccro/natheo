/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Ressource d'administration modifiée dans un formulaire : lecture avec son ETag, enregistrement avec If-Match,
 * conflit (412), erreurs de validation (422) et suivi du départ du formulaire
 */
import { getCurrentInstance, nextTick, onBeforeUnmount, ref, shallowRef, type Ref, type ShallowRef } from 'vue';
import { notify } from '@/vue/Composables/Global/toast';
import { ApiError } from '@/vue/Composables/Global/Api/ApiError';
import { leaveGuard } from '@/vue/Composables/Admin/leaveGuard';
import { useFormErrors, violationsFrom } from '@/vue/Composables/Admin/Ui/useFormErrors';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { ApiResource } from '@/ts/Global/Api/Hydra.type';
import type { FormErrors } from '@/ts/Admin/Ui/Form/UiForm.type';
import type { Translator } from '@/vue/Composables/Admin/useTranslate';

export interface AdminResource<T> {
  /** Données du formulaire, null avant la lecture */
  data: Ref<T | null>;
  /** Identifiant, null pour une création pas encore enregistrée */
  id: Ref<string | number | null>;
  etag: Ref<string | null>;
  loading: Ref<boolean>;
  saving: Ref<boolean>;
  /** La ressource a changé depuis sa lecture : à recharger avant d'enregistrer */
  conflict: Ref<boolean>;
  error: ShallowRef<ApiError | null>;
  errors: FormErrors;
  /** Lit la ressource, ou part de initial pour une création */
  load(): Promise<void>;
  /** Enregistre data (ou body) ; renvoie la ressource enregistrée, null en cas d'échec */
  save(body?: object): Promise<T | null>;
  /** Supprime la ressource ; true si elle l'est */
  remove(): Promise<boolean>;
  /** Arrête le suivi du départ du formulaire */
  dispose(): void;
}

export interface AdminResourceOptions<T> {
  /** Données d'une création */
  initial?: () => T;
  /** Clé du formulaire dans le suivi du départ */
  leaveKey?: string;
}

/**
 * Crée la ressource
 * @param api client de l'API
 * @param t traducteur de l'application
 * @param collection chemin de la collection (/admin/tags)
 * @param id identifiant, null pour une création
 * @param options
 */
export function createAdminResource<T extends object>(
  api: ApiClient,
  t: Translator['t'],
  collection: string,
  id: string | number | null,
  options: AdminResourceOptions<T> = {}
): AdminResource<T> {
  const data = ref<T | null>(null) as Ref<T | null>;
  const currentId = ref<string | number | null>(id);
  const etag = ref<string | null>(null);
  const loading = ref(false);
  const saving = ref(false);
  const conflict = ref(false);
  const error = shallowRef<ApiError | null>(null);
  const errors = useFormErrors();
  const leaveKey = options.leaveKey ?? `resource:${collection}`;

  const itemPath = (): string => `${collection}/${currentId.value}`;

  const accept = (resource: ApiResource<T>): void => {
    data.value = resource.data;
    etag.value = resource.etag;
    if (currentId.value === null) {
      currentId.value = (resource.data as { id?: string | number }).id ?? null;
    }
  };

  const fail = (caught: unknown): void => {
    error.value = caught instanceof ApiError ? caught : ApiError.fromNetwork(caught);
    if (error.value.status === 412) {
      conflict.value = true;
      notify('warning', t('admin_app.resource.conflict_title'), t('admin_app.resource.conflict_text'));
    } else if (error.value.status === 422) {
      errors.set(violationsFrom(error.value.body));
    } else {
      notify('danger', error.value.title, error.value.detail ?? undefined);
    }
  };

  return {
    data,
    id: currentId,
    etag,
    loading,
    saving,
    conflict,
    error,
    errors,
    async load(): Promise<void> {
      loading.value = true;
      error.value = null;
      try {
        if (currentId.value === null) {
          data.value = options.initial?.() ?? null;
          etag.value = null;
        } else {
          accept(await api.getResource<T>(itemPath()));
        }
        conflict.value = false;
        errors.clearAll();
        // Un éditeur peut normaliser les données à son montage : la référence est prise après le rendu
        await nextTick();
        leaveGuard.track(leaveKey, () => data.value);
      } catch (caught) {
        fail(caught);
      } finally {
        loading.value = false;
      }
    },
    async save(body?: object): Promise<T | null> {
      const payload = body ?? data.value;
      if (payload === null) return null;
      saving.value = true;
      error.value = null;
      errors.clearAll();
      try {
        accept(
          currentId.value === null
            ? await api.postResource<T>(collection, payload)
            : await api.patchResource<T>(itemPath(), payload, { ifMatch: etag.value })
        );
        conflict.value = false;
        leaveGuard.commit(leaveKey);
        return data.value;
      } catch (caught) {
        fail(caught);
        return null;
      } finally {
        saving.value = false;
      }
    },
    async remove(): Promise<boolean> {
      if (currentId.value === null) return false;
      saving.value = true;
      error.value = null;
      try {
        await api.delete(itemPath(), { ifMatch: etag.value });
        leaveGuard.untrack(leaveKey);
        return true;
      } catch (caught) {
        fail(caught);
        return false;
      } finally {
        saving.value = false;
      }
    },
    dispose(): void {
      leaveGuard.untrack(leaveKey);
    },
  };
}

/**
 * Ressource d'un écran, dont le suivi du départ s'arrête avec lui
 * @param collection chemin de la collection (/admin/tags)
 * @param id identifiant, null pour une création
 * @param options
 */
export function useAdminResource<T extends object>(
  collection: string,
  id: string | number | null,
  options: AdminResourceOptions<T> = {}
): AdminResource<T> {
  const { api, t } = useAdminContext();
  const resource = createAdminResource<T>(api, t, collection, id, options);
  if (getCurrentInstance()) {
    onBeforeUnmount(() => resource.dispose());
  }
  return resource;
}
