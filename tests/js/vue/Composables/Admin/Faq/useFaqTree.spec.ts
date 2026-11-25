import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { createFaqTree, FAQ_LINE_LEAVE_KEY, FAQ_RESOURCE } from '@/vue/Composables/Admin/Faq/useFaqTree';
import { leaveGuard } from '@/vue/Composables/Admin/leaveGuard';
import { onResourceChange } from '@/vue/Composables/Admin/Screen/resourceChanges';
import { notificationState } from '@/vue/Composables/Global/toast';
import { ApiError } from '@/vue/Composables/Global/Api/ApiError';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { AdminFaqTreeCategory } from '@/ts/Admin/Api/AdminFaq.type';

const t = (key: string, params: Record<string, unknown> = {}): string =>
  Object.keys(params).length ? `${key} ${JSON.stringify(params)}` : key;

const question = (id: number, position: number, disabled = false) => ({
  '@id': `/api/v2/admin/faq_questions/${id}`,
  id,
  position,
  disabled,
  title: `Q${id}`,
});

const category = (id: number, position: number, questions: ReturnType<typeof question>[], disabled = false) => ({
  '@id': `/api/v2/admin/faq_categories/${id}`,
  id,
  position,
  disabled,
  title: `C${id}`,
  translations: [{ locale: 'fr', title: `C${id}` }],
  questions,
});

const initialTree = (): AdminFaqTreeCategory[] => [
  category(1, 1, [question(10, 1), question(11, 2, true)]),
  category(2, 2, []),
];

function treeWith(api: Record<string, unknown>, confirmed = true) {
  const source = {};
  const confirm = vi.fn().mockResolvedValue(confirmed);
  const client = {
    get: vi.fn().mockResolvedValue({ categories: initialTree() }),
    patch: vi.fn().mockResolvedValue({}),
    delete: vi.fn().mockResolvedValue(undefined),
    ...api,
  } as unknown as ApiClient;
  const tree = createFaqTree(
    { api: client, t, confirm, faqId: 3, faqIri: '/api/v2/admin/faqs/3', locales: ['fr', 'en', 'es'], source },
    initialTree()
  );
  return { tree, client: client as unknown as Record<string, ReturnType<typeof vi.fn>>, confirm, source };
}

beforeEach(() => {
  notificationState.items.splice(0);
});
afterEach(() => leaveGuard.untrack(FAQ_LINE_LEAVE_KEY));

describe('createFaqTree', () => {
  it('déplace une question vers une autre catégorie, relit l’arbre et l’annonce à la liste', async () => {
    const { tree, client, source } = treeWith({});
    const changed = vi.fn();
    const stop = onResourceChange(FAQ_RESOURCE, changed);
    const [first, second] = tree.categories.value;

    await tree.moveQuestion(first.questions[0], second, 1);

    expect(client.patch).toHaveBeenCalledWith('/api/v2/admin/faq_questions/10', {
      category: '/api/v2/admin/faq_categories/2',
      position: 1,
    });
    expect(client.get).toHaveBeenCalledWith('/admin/faqs/3');
    expect(changed).toHaveBeenCalledWith({ ids: [3], source });
    expect(tree.savedAt.value).toBeInstanceOf(Date);
    expect(notificationState.items[0].title).toBe('faq.notify.question_moved_to {"category":"C2"}');
    stop();
  });

  it('affiche le nouvel ordre avant la réponse de l’API', async () => {
    let answer: (value: unknown) => void = () => undefined;
    const { tree } = treeWith({ patch: vi.fn(() => new Promise((resolve) => (answer = resolve))) });
    const [first] = tree.categories.value;

    const moving = tree.moveQuestion(first.questions[1], first, 1);
    expect(tree.categories.value[0].questions.map((q) => [q.id, q.position])).toEqual([
      [11, 1],
      [10, 2],
    ]);
    answer({});
    await moving;

    const toOther = tree.moveQuestion(tree.categories.value[0].questions[0], tree.categories.value[1], 1);
    expect(tree.categories.value[0].questions.map((q) => [q.id, q.position])).toEqual([[11, 1]]);
    expect(tree.categories.value[1].questions.map((q) => q.id)).toEqual([10]);
    answer({});
    await toOther;

    const category = tree.moveCategory(tree.categories.value[1], 1);
    expect(tree.categories.value.map((c) => [c.id, c.position])).toEqual([
      [2, 1],
      [1, 2],
    ]);
    answer({});
    await category;
  });

  it('n’envoie rien pour un déplacement sur place, seulement la position dans la même catégorie', async () => {
    const { tree, client } = treeWith({});
    const [first] = tree.categories.value;
    await tree.moveQuestion(first.questions[0], first, 1);
    expect(client.patch).not.toHaveBeenCalled();
    await tree.moveQuestion(first.questions[0], first, 2);
    expect(client.patch).toHaveBeenCalledWith('/api/v2/admin/faq_questions/10', { position: 2 });
  });

  it('demande confirmation avant de désactiver une catégorie, en annonçant ses questions actives', async () => {
    const refused = treeWith({}, false);
    await refused.tree.toggleCategory(refused.tree.categories.value[0]);
    expect(refused.confirm).toHaveBeenCalledWith(
      expect.objectContaining({ message: 'faq.dialog.category_disable_text {"title":"C1","count":1}' })
    );
    expect(refused.client.patch).not.toHaveBeenCalled();

    const { tree, client } = treeWith({});
    await tree.toggleCategory(tree.categories.value[0]);
    expect(client.patch).toHaveBeenCalledWith('/api/v2/admin/faq_categories/1', { disabled: true });
  });

  it('active une catégorie sans confirmation et signale les questions restées désactivées', async () => {
    const { tree, client, confirm } = treeWith({});
    tree.categories.value[0].disabled = true;
    await tree.toggleCategory(tree.categories.value[0]);
    expect(confirm).not.toHaveBeenCalled();
    expect(client.patch).toHaveBeenCalledWith('/api/v2/admin/faq_categories/1', { disabled: false });
    expect(notificationState.items[0].title).toBe('faq.notify.category_enabled {"count":1}');
  });

  it('affiche l’erreur de l’API et relit l’arbre', async () => {
    const error = new ApiError(409, 'Conflit', 'Déjà supprimée', null);
    const { tree, client } = treeWith({ delete: vi.fn().mockRejectedValue(error) });
    await tree.deleteQuestion(tree.categories.value[0].questions[0]);
    expect(notificationState.items[0]).toMatchObject({ type: 'danger', title: 'Conflit', text: 'Déjà supprimée' });
    expect(client.get).toHaveBeenCalledOnce();
    expect(tree.savedAt.value).toBeNull();
  });

  it('ouvre une ligne à la fois et demande confirmation pour quitter une ligne modifiée', async () => {
    const getResource = vi.fn().mockResolvedValue({
      data: { id: 10, category: '/api/v2/admin/faq_categories/1', disabled: false, translations: [] },
      etag: '"v1"',
    });
    const { tree, confirm } = treeWith({ getResource }, false);
    const [first] = tree.categories.value;

    expect(await tree.openQuestion(first, first.questions[0])).toBe(true);
    expect(getResource).toHaveBeenCalledWith('/admin/faq_questions/10');
    expect(tree.lineDirty.value).toBe(false);
    tree.line.value!.resource.data.value!.translations.push({ locale: 'fr', title: 'Modifiée', answer: 'R' } as never);
    expect(tree.lineDirty.value).toBe(true);

    expect(await tree.openCategory(first)).toBe(false);
    expect(confirm).toHaveBeenCalledWith(expect.objectContaining({ title: 'faq.dialog.leave_title' }));
    expect(tree.line.value?.type).toBe('question');
  });

  it('crée une catégorie avec les titres dans l’ordre des langues, ferme la ligne et relit l’arbre', async () => {
    const postResource = vi.fn().mockResolvedValue({ data: { id: 5 }, etag: '"v1"' });
    const { tree, client } = treeWith({ postResource });
    await tree.openCategory();
    const data = tree.line.value!.resource.data.value!;
    data.translations = [
      { locale: 'es', title: 'Envío' },
      { locale: 'fr', title: 'Livraison' },
      { locale: 'en', title: 'Shipping' },
    ];

    await tree.saveLine();

    expect(postResource).toHaveBeenCalledWith('/admin/faq_categories', {
      faq: '/api/v2/admin/faqs/3',
      translations: [
        { locale: 'fr', title: 'Livraison' },
        { locale: 'en', title: 'Shipping' },
        { locale: 'es', title: 'Envío' },
      ],
    });
    expect(tree.line.value).toBeNull();
    expect(client.get).toHaveBeenCalledOnce();
    expect(notificationState.items[0].title).toBe('faq.notify.category_created');
  });

  it('garde la ligne ouverte quand l’API refuse les données', async () => {
    const error = new ApiError(422, 'Invalide', null, {
      violations: [{ propertyPath: 'translations[0].title', message: 'Vide' }],
    });
    const postResource = vi.fn().mockRejectedValue(error);
    const { tree } = treeWith({ postResource });
    await tree.openCategory();
    await tree.saveLine();
    expect(tree.line.value?.type).toBe('category');
    expect(tree.line.value!.resource.errors.count.value).toBe(1);
  });

  it('ferme la ligne d’une question supprimée avec sa catégorie', async () => {
    const getResource = vi.fn().mockResolvedValue({ data: { id: 10, translations: [] }, etag: '"v1"' });
    const { tree, client } = treeWith({ getResource });
    const [first] = tree.categories.value;
    await tree.openQuestion(first, first.questions[0]);
    await tree.deleteCategory(first);
    expect(client.delete).toHaveBeenCalledWith('/api/v2/admin/faq_categories/1');
    expect(tree.line.value).toBeNull();
  });
});
