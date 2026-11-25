import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, h, ref } from 'vue';
import { flushPromises, mount } from '@vue/test-utils';
import FaqContentField from '@admin/Modules/Faq/FaqContentField.vue';
import { ADMIN_CONTEXT, createAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { loadAdminEditor } from '@/vue/Composables/Admin/Editor/loadAdminEditor';
import { FAQ_LINE_LEAVE_KEY } from '@/vue/Composables/Admin/Faq/useFaqTree';
import { leaveGuard } from '@/vue/Composables/Admin/leaveGuard';
import { useConfirm } from '@/vue/Composables/Admin/useConfirm';
import { UI_LABELS } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { bootstrap } from '@tests/vue/Composables/Admin/support';
import { labels } from '@tests/vue/Components/Admin/Ui/support';
import type { FormScreenState } from '@/ts/Admin/Screen/FormScreen.type';
import type { AdminFaq } from '@/ts/Admin/Api/AdminFaq.type';

vi.mock('@/vue/Composables/Admin/Editor/loadAdminEditor', () => ({ loadAdminEditor: vi.fn() }));

/** Éditeur de test : affiche sa valeur et son mode */
const FakeEditor = defineComponent({
  props: ['modelValue', 'readonly'],
  setup(props) {
    return () => h('div', { class: 'fake-editor', 'data-readonly': String(!!props.readonly) }, props.modelValue);
  },
});

const t = (key: string, params: Record<string, unknown> = {}): string =>
  Object.keys(params).length ? `${key} ${JSON.stringify(params)}` : key;

const faq = (): Partial<AdminFaq> => ({
  id: 3,
  permissions: { edit: true, disable: true, delete: true },
  categories: [
    {
      '@id': '/api/v2/admin/faq_categories/1',
      id: 1,
      position: 1,
      disabled: false,
      title: 'Achat',
      translations: [],
      questions: [
        { '@id': '/api/v2/admin/faq_questions/10', id: 10, position: 1, disabled: false, title: 'Délai ?' },
        { '@id': '/api/v2/admin/faq_questions/11', id: 11, position: 2, disabled: true, title: 'Retour ?' },
      ],
    },
    {
      '@id': '/api/v2/admin/faq_categories/2',
      id: 2,
      position: 2,
      disabled: true,
      title: 'Livraison',
      translations: [],
      questions: [],
    },
  ],
});

function mountTree(canEdit = true) {
  const api = {
    get: vi.fn().mockResolvedValue(faq()),
    patch: vi.fn().mockResolvedValue({}),
    getResource: vi.fn().mockResolvedValue({
      data: {
        id: 10,
        category: '/api/v2/admin/faq_categories/1',
        disabled: false,
        translations: [{ locale: 'fr', title: 'Délai ?', answer: 'Trois jours.' }],
      },
      etag: '"v1"',
    }),
  };
  const screen = {
    canEdit: ref(canEdit),
    resource: {},
    currentLocale: 'fr',
    locales: ['fr', 'en', 'es'],
  } as unknown as FormScreenState<AdminFaq>;
  const context = { ...createAdminContext(bootstrap()), t, api };
  const wrapper = mount(FaqContentField, {
    props: { data: faq(), screen },
    attachTo: document.body,
    global: { provide: { [ADMIN_CONTEXT as symbol]: context, [UI_LABELS as symbol]: labels } },
  });
  return { wrapper, api };
}

beforeEach(() => {
  vi.mocked(loadAdminEditor).mockResolvedValue({ id: 'fake', component: FakeEditor });
});
afterEach(() => {
  leaveGuard.untrack(FAQ_LINE_LEAVE_KEY);
  document.body.innerHTML = '';
});

describe('FaqContentField', () => {
  it('affiche les catégories, leurs questions, les compteurs et les états désactivés', () => {
    const { wrapper } = mountTree();
    expect(wrapper.text()).toContain('faq.content.count {"categories":2,"questions":2}');
    expect(wrapper.findAll('[aria-expanded] .truncate').map((title) => title.text())).toEqual(['Achat', 'Livraison']);
    expect(wrapper.text()).toContain('Retour ?');
    expect(wrapper.text()).toContain('faq.content.empty_category');
    expect(wrapper.findAll('[data-faq-handle]')).toHaveLength(4);
    wrapper.unmount();
  });

  it('replie une catégorie et ouvre une nouvelle question dans sa ligne', async () => {
    const { wrapper } = mountTree();
    const toggle = wrapper.get('button[aria-controls="faq-category-1"]');
    await toggle.trigger('click');
    expect(toggle.attributes('aria-expanded')).toBe('false');
    expect(wrapper.get('#faq-category-1').isVisible()).toBe(false);

    const add = wrapper.findAll('button').find((button) => button.text() === 'faq.content.add_question');
    await add!.trigger('click');
    await flushPromises();
    expect(wrapper.get('[data-faq-line]').text()).toContain('faq.question.heading_new {"category":"Achat"}');
    expect(wrapper.text()).toContain('faq.line.create');
    wrapper.unmount();
  });

  it('descend une catégorie depuis son menu', async () => {
    const { wrapper, api } = mountTree();
    const menu = wrapper
      .findAll('button[aria-haspopup="menu"]')
      .find((button) => button.attributes('aria-label') === 'faq.content.menu_category {"title":"Achat"}');
    await menu!.trigger('click');
    const down = [...document.querySelectorAll<HTMLElement>('[role="menuitem"]')].find(
      (item) => item.textContent?.trim() === 'faq.content.action_down'
    );
    down!.click();
    await flushPromises();
    expect(api.patch).toHaveBeenCalledWith('/api/v2/admin/faq_categories/1', { position: 2 });
    expect(api.get).toHaveBeenCalledWith('/admin/faqs/3');
    wrapper.unmount();
  });

  it('demande confirmation avant de quitter la ligne modifiée', async () => {
    const { wrapper } = mountTree();
    await wrapper
      .findAll('button')
      .find((button) => button.text() === 'Délai ?')!
      .trigger('click');
    await flushPromises();
    await wrapper.get('[data-faq-line] input').setValue('Délai de livraison ?');
    expect(wrapper.text()).toContain('faq.line.dirty');

    await wrapper
      .findAll('button')
      .find((button) => button.text() === 'faq.line.cancel')!
      .trigger('click');
    expect(useConfirm().current.value?.title).toBe('faq.dialog.leave_title');
    useConfirm().answer(true);
    await flushPromises();
    expect(wrapper.find('[data-faq-line]').exists()).toBe(false);
    wrapper.unmount();
  });

  it('en lecture seule, n’affiche ni poignée ni action et ouvre la question sans modification', async () => {
    const { wrapper, api } = mountTree(false);
    expect(wrapper.find('[data-faq-handle]').exists()).toBe(false);
    expect(wrapper.find('[aria-haspopup="menu"]').exists()).toBe(false);
    expect(wrapper.text()).not.toContain('faq.content.add_category');

    await wrapper
      .findAll('button')
      .find((button) => button.text() === 'Délai ?')!
      .trigger('click');
    await flushPromises();
    expect(api.getResource).toHaveBeenCalledWith('/admin/faq_questions/10');
    const line = wrapper.get('[data-faq-line]');
    expect(line.text()).toContain('faq.question.heading_view');
    expect(line.get('input').attributes('disabled')).toBeDefined();
    expect(line.text()).not.toContain('faq.line.save');
    wrapper.unmount();
  });
});
