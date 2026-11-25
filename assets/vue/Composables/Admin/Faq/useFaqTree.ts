/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Arbre d'une FAQ dans son formulaire : catégories et questions déplacées, activées et supprimées aussitôt (arbre
 * relu après chaque écriture), une ligne ouverte à la fois pour modifier une catégorie ou une question
 */
import { computed, getCurrentInstance, onBeforeUnmount, ref, shallowRef, type Ref, type ShallowRef } from 'vue';
import { notify } from '@/vue/Composables/Global/toast';
import { ApiError } from '@/vue/Composables/Global/Api/ApiError';
import { leaveGuard } from '@/vue/Composables/Admin/leaveGuard';
import { createAdminResource, type AdminResource } from '@/vue/Composables/Admin/useAdminResource';
import { notifyResourceChange } from '@/vue/Composables/Admin/Screen/resourceChanges';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { useConfirm, type ConfirmRequest } from '@/vue/Composables/Admin/useConfirm';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { Translator } from '@/vue/Composables/Admin/useTranslate';
import type {
  AdminFaq,
  AdminFaqCategory,
  AdminFaqQuestion,
  AdminFaqTreeCategory,
  AdminFaqTreeQuestion,
} from '@/ts/Admin/Api/AdminFaq.type';

/** Collection de la FAQ, annoncée après chaque écriture pour la liste voisine */
export const FAQ_RESOURCE = '/admin/faqs';
const CATEGORY_RESOURCE = '/admin/faq_categories';
const QUESTION_RESOURCE = '/admin/faq_questions';
/** Clé de la ligne ouverte dans le suivi du départ */
export const FAQ_LINE_LEAVE_KEY = 'faq_tree_line';

/** Ligne ouverte : catégorie ou question existante, ou nouvelle (id null) */
export type FaqTreeLine =
  | { type: 'category'; id: number | null; resource: AdminResource<AdminFaqCategory>; copying: Ref<boolean> }
  | { type: 'question'; id: number | null; categoryId: number; resource: AdminResource<AdminFaqQuestion> };

export interface FaqTreeDeps {
  api: ApiClient;
  t: Translator['t'];
  confirm: (request: ConfirmRequest) => Promise<boolean>;
  faqId: number;
  /** IRI de la FAQ, envoyée à la création d'une catégorie */
  faqIri: string;
  locales: string[];
  /** Ressource du formulaire de la FAQ, qui ignore les écritures annoncées par l'arbre */
  source: object;
}

export interface FaqTree {
  categories: Ref<AdminFaqTreeCategory[]>;
  questionCount: Ref<number>;
  /** Écriture de l'arbre en cours */
  busy: Ref<boolean>;
  /** Heure de la dernière écriture réussie */
  savedAt: Ref<Date | null>;
  line: ShallowRef<FaqTreeLine | null>;
  /** Remplace l'arbre, à la lecture de la FAQ */
  setCategories(categories: AdminFaqTreeCategory[]): void;
  reload(): Promise<void>;
  categoryOf(questionId: number): AdminFaqTreeCategory | undefined;
  moveCategory(category: AdminFaqTreeCategory, position: number): Promise<void>;
  /** Place la question à position (à partir de 1) dans target */
  moveQuestion(question: AdminFaqTreeQuestion, target: AdminFaqTreeCategory, position: number): Promise<void>;
  toggleCategory(category: AdminFaqTreeCategory): Promise<void>;
  toggleQuestion(question: AdminFaqTreeQuestion): Promise<void>;
  deleteCategory(category: AdminFaqTreeCategory): Promise<void>;
  deleteQuestion(question: AdminFaqTreeQuestion): Promise<void>;
  /** Ouvre une catégorie, une nouvelle sans argument ; false si l'utilisateur garde la ligne modifiée */
  openCategory(category?: AdminFaqTreeCategory): Promise<boolean>;
  openQuestion(category: AdminFaqTreeCategory, question?: AdminFaqTreeQuestion): Promise<boolean>;
  /** Relit la ligne ouverte, après un conflit */
  reloadLine(): Promise<void>;
  /** Ferme la ligne, après confirmation si elle est modifiée */
  cancelLine(): Promise<void>;
  saveLine(): Promise<void>;
  /** Active l'élément de la ligne ouverte, et sa catégorie pour une question */
  enableLine(): Promise<void>;
  lineDirty: Ref<boolean>;
  dispose(): void;
}

/**
 * Crée l'arbre
 * @param deps
 * @param initial catégories lues avec la FAQ
 */
export function createFaqTree(deps: FaqTreeDeps, initial: AdminFaqTreeCategory[]): FaqTree {
  const { api, t, confirm, faqId, faqIri, locales, source } = deps;
  const categories = ref<AdminFaqTreeCategory[]>(initial);
  const busy = ref(false);
  const savedAt = ref<Date | null>(null);
  const line = shallowRef<FaqTreeLine | null>(null);
  // Textes de la ligne ouverte tels que lus, pour distinguer une écriture de l'arbre d'une modification d'ailleurs
  let lineTexts: string | null = null;
  const textsOf = (data: { translations?: unknown } | null | undefined): string =>
    JSON.stringify(data?.translations ?? null);

  const categoryOf = (questionId: number): AdminFaqTreeCategory | undefined =>
    categories.value.find((category) => category.questions.some((question) => question.id === questionId));

  const reload = async (): Promise<void> => {
    const faq = await api.get<AdminFaq>(`${FAQ_RESOURCE}/${faqId}`);
    categories.value = faq.categories ?? [];
  };

  /**
   * Reprend la version de la ligne ouverte après une écriture de l'arbre, qui peut changer sa position, son statut
   * ou sa catégorie ; des textes modifiés ailleurs gardent l'ancienne version, et le conflit à l'enregistrement
   */
  const refreshLine = async (): Promise<void> => {
    const current = line.value;
    const data = current?.resource.data.value;
    if (!current || !data || current.id === null || current.resource.conflict.value) return;
    const collection = current.type === 'category' ? CATEGORY_RESOURCE : QUESTION_RESOURCE;
    const fresh = await api.getResource<AdminFaqCategory | AdminFaqQuestion>(`${collection}/${current.id}`);
    if (line.value !== current || textsOf(fresh.data) !== lineTexts) return;
    const wasDirty = leaveGuard.isDirty(FAQ_LINE_LEAVE_KEY);
    data.position = fresh.data.position;
    data.disabled = fresh.data.disabled;
    if (current.type === 'question') {
      (data as AdminFaqQuestion).category = (fresh.data as AdminFaqQuestion).category;
    }
    current.resource.etag.value = fresh.etag;
    if (!wasDirty) leaveGuard.commit(FAQ_LINE_LEAVE_KEY);
  };

  /** Écriture puis relecture de l'arbre et de la ligne ouverte, notification et annonce à la liste */
  const run = async (write: () => Promise<unknown>, message: () => string): Promise<boolean> => {
    busy.value = true;
    try {
      await write();
      await Promise.all([reload(), refreshLine().catch(() => undefined)]);
      savedAt.value = new Date();
      notifyResourceChange(FAQ_RESOURCE, { ids: [faqId], source });
      notify('success', message());
      return true;
    } catch (caught) {
      const error = caught instanceof ApiError ? caught : ApiError.fromNetwork(caught);
      notify('danger', error.title, error.detail ?? undefined);
      await reload().catch(() => undefined);
      return false;
    } finally {
      busy.value = false;
    }
  };

  const closeLine = (): void => {
    line.value?.resource.dispose();
    line.value = null;
    lineTexts = null;
  };

  const lineDirty = computed(() => line.value !== null && leaveGuard.isDirty(FAQ_LINE_LEAVE_KEY));

  /** Ferme la ligne ouverte ; false si elle est modifiée et que l'utilisateur la garde */
  const releaseLine = async (): Promise<boolean> => {
    if (line.value === null) return true;
    if (leaveGuard.isDirty(FAQ_LINE_LEAVE_KEY)) {
      const confirmed = await confirm({
        title: t('faq.dialog.leave_title'),
        message: t('faq.dialog.leave_text'),
        tone: 'danger',
        confirmLabel: t('faq.dialog.leave_confirm'),
      });
      if (!confirmed) return false;
    }
    closeLine();
    return true;
  };

  /** Ferme la ligne ouverte quand l'action supprime son élément */
  const dropLineOf = (type: 'category' | 'question', ids: number[]): void => {
    const current = line.value;
    if (current === null) return;
    const concerned =
      (current.type === type && current.id !== null && ids.includes(current.id)) ||
      (type === 'category' && current.type === 'question' && ids.includes(current.categoryId));
    if (concerned) closeLine();
  };

  const emptyTitles = () => locales.map((locale) => ({ locale, title: '' }));

  /** Place item à position (à partir de 1) dans list, renumérotée comme le fera le serveur */
  const placeAt = <T extends { id: number; position: number }>(list: T[], item: T, position: number): T[] => {
    const others = list.filter((entry) => entry.id !== item.id);
    others.splice(Math.max(0, Math.min(position - 1, others.length)), 0, item);
    others.forEach((entry, index) => (entry.position = index + 1));
    return others;
  };

  const tree: FaqTree = {
    categories,
    questionCount: computed(() => categories.value.reduce((count, category) => count + category.questions.length, 0)),
    busy,
    savedAt,
    line,
    lineDirty,
    categoryOf,
    reload,
    setCategories(list: AdminFaqTreeCategory[]): void {
      categories.value = list;
    },

    async moveCategory(category, position): Promise<void> {
      if (position === category.position) return;
      // Ordre affiché tout de suite, l'arbre relu le confirme
      categories.value = placeAt(categories.value, category, position);
      await run(
        () => api.patch(category['@id'], { position }),
        () => t('faq.notify.category_moved', { position })
      );
    },

    async moveQuestion(question, target, position): Promise<void> {
      const from = categoryOf(question.id);
      const changesCategory = from !== undefined && from.id !== target.id;
      if (!changesCategory && position === question.position) return;
      // Ordre affiché tout de suite, l'arbre relu le confirme
      if (changesCategory) {
        from.questions = from.questions.filter((entry) => entry.id !== question.id);
        from.questions.forEach((entry, index) => (entry.position = index + 1));
      }
      target.questions = placeAt(target.questions, question, position);
      await run(
        () => api.patch(question['@id'], changesCategory ? { category: target['@id'], position } : { position }),
        () =>
          changesCategory
            ? t('faq.notify.question_moved_to', { category: target.title })
            : t('faq.notify.question_moved', { position })
      );
    },

    async toggleCategory(category): Promise<void> {
      if (category.disabled) {
        const stillDisabled = category.questions.filter((question) => question.disabled).length;
        await run(
          () => api.patch(category['@id'], { disabled: false }),
          () => t('faq.notify.category_enabled', { count: stillDisabled })
        );
        return;
      }
      const active = category.questions.filter((question) => !question.disabled).length;
      const confirmed = await confirm({
        title: t('faq.dialog.category_disable_title'),
        message: t('faq.dialog.category_disable_text', { title: category.title, count: active }),
        tone: 'primary',
        icon: 'ban',
        confirmLabel: t('faq.dialog.category_disable_confirm'),
        confirmIcon: 'ban',
      });
      if (!confirmed) return;
      await run(
        () => api.patch(category['@id'], { disabled: true }),
        () => t('faq.notify.category_disabled', { count: category.questions.length })
      );
    },

    async toggleQuestion(question): Promise<void> {
      const disabled = !question.disabled;
      await run(
        () => api.patch(question['@id'], { disabled }),
        () => (disabled ? t('faq.notify.question_disabled') : t('faq.notify.question_enabled'))
      );
    },

    async deleteCategory(category): Promise<void> {
      const confirmed = await confirm({
        title: t('faq.dialog.category_delete_title'),
        message: t('faq.dialog.category_delete_text', { title: category.title, count: category.questions.length }),
        tone: 'danger',
        icon: 'trash',
        confirmLabel: t('faq.dialog.category_delete_confirm'),
        confirmIcon: 'trash',
      });
      if (!confirmed) return;
      const count = category.questions.length;
      dropLineOf('category', [category.id]);
      await run(
        () => api.delete(category['@id']),
        () => t('faq.notify.category_deleted', { count })
      );
    },

    async deleteQuestion(question): Promise<void> {
      const confirmed = await confirm({
        title: t('faq.dialog.question_delete_title'),
        message: t('faq.dialog.question_delete_text', { title: question.title }),
        tone: 'danger',
        icon: 'trash',
        confirmLabel: t('faq.dialog.question_delete_confirm'),
        confirmIcon: 'trash',
      });
      if (!confirmed) return;
      dropLineOf('question', [question.id]);
      await run(
        () => api.delete(question['@id']),
        () => t('faq.notify.question_deleted')
      );
    },

    async openCategory(category?: AdminFaqTreeCategory): Promise<boolean> {
      if (!(await releaseLine())) return false;
      const resource = createAdminResource<AdminFaqCategory>(api, t, CATEGORY_RESOURCE, category?.id ?? null, {
        initial: () => ({ faq: faqIri, disabled: false, translations: emptyTitles() }),
        leaveKey: FAQ_LINE_LEAVE_KEY,
      });
      line.value = { type: 'category', id: category?.id ?? null, resource, copying: ref(category === undefined) };
      await resource.load();
      lineTexts = textsOf(resource.data.value);
      return true;
    },

    async openQuestion(category: AdminFaqTreeCategory, question?: AdminFaqTreeQuestion): Promise<boolean> {
      if (!(await releaseLine())) return false;
      const resource = createAdminResource<AdminFaqQuestion>(api, t, QUESTION_RESOURCE, question?.id ?? null, {
        initial: () => ({
          category: category['@id'],
          disabled: false,
          translations: locales.map((locale) => ({ locale, title: '', answer: '' })),
        }),
        leaveKey: FAQ_LINE_LEAVE_KEY,
      });
      line.value = { type: 'question', id: question?.id ?? null, categoryId: category.id, resource };
      await resource.load();
      lineTexts = textsOf(resource.data.value);
      return true;
    },

    async reloadLine(): Promise<void> {
      const current = line.value;
      if (!current) return;
      await current.resource.load();
      if (line.value === current) lineTexts = textsOf(current.resource.data.value);
    },

    async cancelLine(): Promise<void> {
      await releaseLine();
    },

    async saveLine(): Promise<void> {
      const current = line.value;
      const data = current?.resource.data.value;
      if (!current || !data || current.resource.conflict.value) return;
      const created = current.id === null;
      const ordered = <R extends { locale: string }>(rows: R[]): R[] =>
        locales.map((locale) => rows.find((row) => row.locale === locale)).filter((row): row is R => !!row);
      let body: object;
      if (current.type === 'category') {
        const translations = ordered((data as AdminFaqCategory).translations);
        body = created ? { faq: faqIri, translations } : { translations };
      } else {
        const translations = ordered((data as AdminFaqQuestion).translations);
        body = created ? { category: (data as AdminFaqQuestion).category, translations } : { translations };
      }
      const saved = await current.resource.save(body);
      if (saved === null) return;
      closeLine();
      let message = created ? t('faq.notify.question_created') : t('faq.notify.question_saved');
      if (current.type === 'category') {
        message = created ? t('faq.notify.category_created') : t('faq.notify.category_saved');
      }
      await run(
        async () => undefined,
        () => message
      );
    },

    async enableLine(): Promise<void> {
      const current = line.value;
      if (!current || current.resource.id.value === null) return;
      const category = current.type === 'question' ? categories.value.find((c) => c.id === current.categoryId) : null;
      // Statut repris par refreshLine(), hors des modifications en cours de la ligne
      await run(
        async () => {
          if (category?.disabled) await api.patch(category['@id'], { disabled: false });
          const path = `${current.type === 'category' ? CATEGORY_RESOURCE : QUESTION_RESOURCE}/${current.resource.id.value}`;
          await api.patchResource(path, { disabled: false }, { ifMatch: current.resource.etag.value });
        },
        () =>
          current.type === 'category'
            ? t('faq.notify.category_enabled', {
                count:
                  categories.value.find((c) => c.id === current.id)?.questions.filter((q) => q.disabled).length ?? 0,
              })
            : t('faq.notify.question_enabled')
      );
    },

    dispose(): void {
      closeLine();
    },
  };
  return tree;
}

/**
 * Arbre de la FAQ d'un formulaire, ligne ouverte fermée avec le composant
 * @param faqId
 * @param initial catégories lues avec la FAQ
 * @param source ressource du formulaire de la FAQ
 */
export function useFaqTree(faqId: number, initial: AdminFaqTreeCategory[], source: object): FaqTree {
  const { api, t, bootstrap } = useAdminContext();
  const tree = createFaqTree(
    {
      api,
      t,
      confirm: useConfirm().confirm,
      faqId,
      faqIri: `${bootstrap.api.prefix}${FAQ_RESOURCE}/${faqId}`,
      locales: bootstrap.locales,
      source,
    },
    initial
  );
  if (getCurrentInstance()) onBeforeUnmount(() => tree.dispose());
  return tree;
}
