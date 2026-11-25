import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, h } from 'vue';
import { flushPromises, mount } from '@vue/test-utils';
import FaqQuestionEditor from '@admin/Modules/Faq/FaqQuestionEditor.vue';
import { ADMIN_CONTEXT, createAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { loadAdminEditor } from '@/vue/Composables/Admin/Editor/loadAdminEditor';
import { createFaqTree, FAQ_LINE_LEAVE_KEY } from '@/vue/Composables/Admin/Faq/useFaqTree';
import { leaveGuard } from '@/vue/Composables/Admin/leaveGuard';
import { UI_LABELS } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { bootstrap } from '@tests/vue/Composables/Admin/support';
import { labels } from '@tests/vue/Components/Admin/Ui/support';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { AdminFaqTreeCategory } from '@/ts/Admin/Api/AdminFaq.type';

vi.mock('@/vue/Composables/Admin/Editor/loadAdminEditor', () => ({ loadAdminEditor: vi.fn() }));

const FakeEditor = defineComponent({
  props: ['modelValue'],
  setup: (props) => () => h('div', { class: 'fake-editor' }, props.modelValue),
});

const t = (key: string, params: Record<string, unknown> = {}): string =>
  Object.keys(params).length ? `${key} ${JSON.stringify(params)}` : key;

const categoryOf = (disabled: boolean): AdminFaqTreeCategory => ({
  '@id': '/api/v2/admin/faq_categories/1',
  id: 1,
  position: 1,
  disabled,
  title: 'Achat',
  translations: [],
  questions: [{ '@id': '/api/v2/admin/faq_questions/10', id: 10, position: 1, disabled: true, title: 'Délai ?' }],
});

async function mountEditor(categoryDisabled: boolean, questionDisabled: boolean, readOnly = false) {
  const category = categoryOf(categoryDisabled);
  const api = {
    getResource: vi.fn().mockResolvedValue({
      data: { id: 10, category: category['@id'], disabled: questionDisabled, translations: [] },
      etag: '"v1"',
    }),
  } as unknown as ApiClient;
  const tree = createFaqTree(
    { api, t, confirm: vi.fn(), faqId: 3, faqIri: '/api/v2/admin/faqs/3', locales: ['fr', 'en', 'es'], source: {} },
    [category]
  );
  await tree.openQuestion(category, category.questions[0]);
  const line = tree.line.value;
  if (line?.type !== 'question') throw new Error('ligne de question attendue');
  const wrapper = mount(FaqQuestionEditor, {
    props: { tree, line, category, readOnly },
    global: {
      provide: {
        [ADMIN_CONTEXT as symbol]: { ...createAdminContext(bootstrap()), t },
        [UI_LABELS as symbol]: labels,
      },
    },
  });
  await flushPromises();
  return wrapper;
}

beforeEach(() => {
  vi.mocked(loadAdminEditor).mockResolvedValue({ id: 'fake', component: FakeEditor });
});
afterEach(() => leaveGuard.untrack(FAQ_LINE_LEAVE_KEY));

describe('FaqQuestionEditor', () => {
  it('rappelle une question et sa catégorie désactivées, avec le bouton qui active les deux', async () => {
    const wrapper = await mountEditor(true, true);
    expect(wrapper.text()).toContain('faq.question.off_both {"category":"Achat"}');
    expect(wrapper.text()).toContain('faq.question.enable_both');
    expect(wrapper.text()).toContain('faq.content.badge_disabled');
  });

  it('rappelle une catégorie désactivée sans bouton pour une question active', async () => {
    const wrapper = await mountEditor(true, false);
    expect(wrapper.text()).toContain('faq.question.off_category {"category":"Achat"}');
    expect(wrapper.text()).not.toContain('faq.question.enable');
  });

  it('n’affiche aucun rappel pour une question affichée sur le site', async () => {
    const wrapper = await mountEditor(false, false);
    expect(wrapper.text()).not.toContain('faq.question.off');
    expect(wrapper.text()).toContain('faq.question.heading_edit');
  });

  it('en lecture seule, rappelle l’état sans bouton et propose de fermer la ligne', async () => {
    const wrapper = await mountEditor(false, true, true);
    expect(wrapper.text()).toContain('faq.question.off');
    expect(wrapper.text()).not.toContain('faq.question.enable');
    expect(wrapper.find('button[aria-label="faq.line.close"]').exists()).toBe(true);
  });
});
