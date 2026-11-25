import { afterEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import FaqCategoryEditor from '@admin/Modules/Faq/FaqCategoryEditor.vue';
import { ADMIN_CONTEXT, createAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { createFaqTree, FAQ_LINE_LEAVE_KEY } from '@/vue/Composables/Admin/Faq/useFaqTree';
import { leaveGuard } from '@/vue/Composables/Admin/leaveGuard';
import { UI_LABELS } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { ApiError } from '@/vue/Composables/Global/Api/ApiError';
import { bootstrap } from '@tests/vue/Composables/Admin/support';
import { labels } from '@tests/vue/Components/Admin/Ui/support';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { AdminFaqTreeCategory } from '@/ts/Admin/Api/AdminFaq.type';

const t = (key: string, params: Record<string, unknown> = {}): string =>
  Object.keys(params).length ? `${key} ${JSON.stringify(params)}` : key;

const category: AdminFaqTreeCategory = {
  '@id': '/api/v2/admin/faq_categories/1',
  id: 1,
  position: 1,
  disabled: true,
  title: 'Achat',
  translations: [{ locale: 'fr', title: 'Achat' }],
  questions: [],
};

async function mountEditor(api: Record<string, unknown>) {
  const client = {
    getResource: vi.fn().mockResolvedValue({
      data: { id: 1, faq: '/api/v2/admin/faqs/3', disabled: true, translations: [{ locale: 'fr', title: 'Achat' }] },
      etag: '"v1"',
    }),
    get: vi.fn().mockResolvedValue({ categories: [category] }),
    ...api,
  } as unknown as ApiClient;
  const tree = createFaqTree(
    {
      api: client,
      t,
      confirm: vi.fn(),
      faqId: 3,
      faqIri: '/api/v2/admin/faqs/3',
      locales: ['fr', 'en', 'es'],
      source: {},
    },
    [category]
  );
  await tree.openCategory(category);
  const line = tree.line.value;
  if (line?.type !== 'category') throw new Error('ligne de catégorie attendue');
  const wrapper = mount(FaqCategoryEditor, {
    props: { tree, line },
    global: {
      provide: {
        [ADMIN_CONTEXT as symbol]: { ...createAdminContext(bootstrap()), t },
        [UI_LABELS as symbol]: labels,
      },
    },
  });
  await flushPromises();
  return { wrapper, tree, client: client as unknown as Record<string, ReturnType<typeof vi.fn>> };
}

afterEach(() => leaveGuard.untrack(FAQ_LINE_LEAVE_KEY));

describe('FaqCategoryEditor', () => {
  it('rappelle une catégorie désactivée avec son bouton d’activation', async () => {
    const { wrapper } = await mountEditor({});
    expect(wrapper.text()).toContain('faq.category.off');
    expect(wrapper.text()).toContain('faq.category.enable');
    expect(wrapper.text()).toContain('faq.content.badge_disabled');
  });

  it('bloque l’enregistrement pendant un conflit, jusqu’à la relecture', async () => {
    const patchResource = vi.fn().mockRejectedValue(new ApiError(412, 'Conflit', null, null));
    const { wrapper, tree, client } = await mountEditor({ patchResource });

    await tree.saveLine();
    await flushPromises();
    const save = wrapper.findAll('button').find((button) => button.text() === 'faq.line.save')!;
    expect(wrapper.text()).toContain('faq.line.conflict_title');
    expect(save.attributes('disabled')).toBeDefined();
    await tree.saveLine();
    expect(patchResource).toHaveBeenCalledOnce();

    const reload = wrapper.findAll('button').find((button) => button.text() === 'faq.line.reload')!;
    await reload.trigger('click');
    await flushPromises();
    expect(client.getResource).toHaveBeenCalledTimes(2);
    expect(wrapper.text()).not.toContain('faq.line.conflict_title');
    expect(save.attributes('disabled')).toBeUndefined();
  });
});
