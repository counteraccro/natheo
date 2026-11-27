import { beforeEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, h } from 'vue';
import { flushPromises, mount } from '@vue/test-utils';
import ScreenFormFields from '@/vue/Components/Admin/App/Screen/ScreenFormFields.vue';
import { ADMIN_CONTEXT, createAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { createAdminResource } from '@/vue/Composables/Admin/useAdminResource';
import { createFormScreen } from '@/vue/Composables/Admin/Screen/useFormScreen';
import { defineFormScreen } from '@/vue/Composables/Admin/Screen/defineScreen';
import { loadAdminEditor } from '@/vue/Composables/Admin/Editor/loadAdminEditor';
import { leaveGuard } from '@/vue/Composables/Admin/leaveGuard';
import { UI_LABELS } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { bootstrap } from '@tests/vue/Composables/Admin/support';
import { labels } from '@tests/vue/Components/Admin/Ui/support';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { AdminCan } from '@/vue/Composables/Admin/useCan';
import type { FormField, FormScreenState } from '@/ts/Admin/Screen/FormScreen.type';

vi.mock('@/vue/Composables/Admin/Editor/loadAdminEditor', () => ({ loadAdminEditor: vi.fn() }));

type Faq = {
  id: number;
  intro: string;
  translations: { locale: string; answer: string }[];
  permissions: { edit: boolean };
};

/** Éditeur de test : expose ses props et émet la valeur au clic */
const FakeEditor = defineComponent({
  props: ['modelValue', 'preset', 'keywords', 'id', 'rows', 'placeholder', 'lang', 'readonly', 'disabled'],
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    return () =>
      h('div', {
        class: 'fake-editor',
        'data-props': JSON.stringify(props),
        onClick: () => emit('update:modelValue', `${props.modelValue} modifié`),
      });
  },
});

const t = (key: string): string => key;

/**
 * Formulaire d'une FAQ avec ses champs
 * @param fields
 * @param edit
 */
async function mountFields(fields: FormField[], edit = true) {
  const getResource = vi.fn().mockResolvedValue({
    data: { id: 3, intro: 'Intro', translations: [{ locale: 'fr', answer: 'Réponse' }], permissions: { edit } },
    etag: '"v1"',
  });
  const resource = createAdminResource<Faq>({ getResource } as unknown as ApiClient, t, '/admin/faqs', 3, {
    leaveKey: 'faq_fields_test',
  });
  const descriptor = defineFormScreen<Faq>({
    resource: '/admin/faqs',
    read: { operation: 'admin_faq_get' },
    title: { create: 'c', edit: 'e', view: 'v' },
    back: 'faq_index',
    editRoute: 'faq_edit',
    leaveKey: 'faq_fields_test',
    initial: () => ({}),
    sections: [{ title: 's', fields }],
    update: { operation: 'admin_faq_patch' },
    messages: { created: 'a', saved: 'b', confirmDelete: 'c', deleted: 'd' },
    rowLabel: (item) => item.intro,
  });
  const can: AdminCan = {
    canOperation: () => true,
    can: (_action, _operation, item) => item?.permissions?.edit !== false,
    hasRole: () => false,
  };
  const screen = createFormScreen(descriptor, {
    t,
    can,
    confirm: vi.fn(),
    router: { push: vi.fn(), replace: vi.fn() },
    resource,
    locales: ['fr', 'en'],
    currentLocale: 'fr',
  });
  await resource.load();
  const wrapper = mount(ScreenFormFields, {
    props: { screen: screen as unknown as FormScreenState, fields },
    global: {
      provide: { [ADMIN_CONTEXT as symbol]: { ...createAdminContext(bootstrap()), t }, [UI_LABELS as symbol]: labels },
    },
  });
  await flushPromises();
  return { wrapper, resource };
}

const propsOf = (element: { attributes: (name: string) => string | undefined }) =>
  JSON.parse(element.attributes('data-props') as string);

describe('ScreenFormFields : éditeur de texte', () => {
  beforeEach(() => {
    vi.mocked(loadAdminEditor).mockResolvedValue({ id: 'fake', component: FakeEditor });
  });

  it('rend un champ textEditor avec ses réglages et écrit sa valeur', async () => {
    const { wrapper, resource } = await mountFields([
      {
        type: 'textEditor',
        name: 'intro',
        label: 'faq.form.intro',
        preset: 'basic',
        rows: 4,
        placeholder: 'faq.form.intro_placeholder',
        keywords: (data) => [{ keyword: `[[faq.${data.id}]]`, label: 'FAQ' }],
      },
    ]);
    const editor = wrapper.get('.fake-editor');
    expect(propsOf(editor)).toMatchObject({
      modelValue: 'Intro',
      preset: 'basic',
      rows: 4,
      placeholder: 'faq.form.intro_placeholder',
      keywords: [{ keyword: '[[faq.3]]', label: 'FAQ' }],
      readonly: false,
    });
    expect(wrapper.get('label').text()).toBe('faq.form.intro');
    await editor.trigger('click');
    expect(resource.data.value?.intro).toBe('Intro modifié');
    leaveGuard.untrack('faq_fields_test');
  });

  it('rend un éditeur par langue avec la langue du contenu', async () => {
    const { wrapper, resource } = await mountFields([
      { type: 'translations', name: 'translations', field: 'answer', label: 'faq.form.answer', input: 'textEditor' },
    ]);
    const editor = wrapper.get('.fake-editor');
    expect(propsOf(editor)).toMatchObject({ modelValue: 'Réponse', lang: 'fr' });
    expect(wrapper.find('input[type="text"]').exists()).toBe(false);
    await editor.trigger('click');
    expect(resource.data.value?.translations.find((row) => row.locale === 'fr')?.answer).toBe('Réponse modifié');
    await wrapper.findAll('[role="tab"]')[1].trigger('click');
    await flushPromises();
    // Un onglet visité reste monté, masqué
    expect(wrapper.findAll('.fake-editor').map((element) => propsOf(element).lang)).toEqual(['fr', 'en']);
    leaveGuard.untrack('faq_fields_test');
  });

  it('passe l’éditeur en lecture seule quand l’élément n’est pas modifiable', async () => {
    const { wrapper } = await mountFields([{ type: 'textEditor', name: 'intro', label: 'faq.form.intro' }], false);
    expect(propsOf(wrapper.get('.fake-editor'))).toMatchObject({ readonly: true, preset: 'full', rows: 8 });
    leaveGuard.untrack('faq_fields_test');
  });
});
