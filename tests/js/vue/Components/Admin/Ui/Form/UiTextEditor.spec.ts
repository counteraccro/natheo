import { beforeEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, h, nextTick } from 'vue';
import { flushPromises, mount } from '@vue/test-utils';
import UiField from '@/vue/Components/Admin/Ui/Form/UiField.vue';
import UiTextEditor from '@/vue/Components/Admin/Ui/Form/UiTextEditor.vue';
import { ADMIN_THEME } from '@/vue/Composables/Admin/Screen/adminTheme';
import { loadAdminEditor } from '@/vue/Composables/Admin/Editor/loadAdminEditor';
import { labels, withLabels } from '@tests/vue/Components/Admin/Ui/support';
import { UI_LABELS } from '@/vue/Composables/Admin/Ui/useUiLabels';
import type { AdminEditorProps } from '@/ts/Admin/Editor/AdminEditor.type';

vi.mock('@/vue/Composables/Admin/Editor/loadAdminEditor', () => ({ loadAdminEditor: vi.fn() }));

/** Éditeur de test : affiche ses props et émet la valeur au clic */
const FakeEditor = defineComponent({
  props: [
    'modelValue',
    'preset',
    'keywords',
    'id',
    'labelledBy',
    'label',
    'describedBy',
    'invalid',
    'required',
    'disabled',
    'readonly',
    'rows',
    'placeholder',
    'lang',
    'theme',
  ],
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    return () =>
      h('div', {
        id: props.id,
        class: 'fake-editor',
        tabindex: 0,
        'data-props': JSON.stringify(props),
        onClick: () => emit('update:modelValue', 'modifié'),
      });
  },
});

const propsOf = (wrapper: ReturnType<typeof mount>): AdminEditorProps =>
  JSON.parse(wrapper.get('.fake-editor').attributes('data-props') as string);

describe('UiTextEditor', () => {
  beforeEach(() => {
    vi.mocked(loadAdminEditor).mockReset();
    vi.mocked(loadAdminEditor).mockResolvedValue({ id: 'fake', component: FakeEditor });
  });

  it('montre un chargement puis l’éditeur actif avec l’état du champ', async () => {
    const wrapper = mount(
      UiField,
      withLabels({
        props: { label: 'Réponse', error: 'Obligatoire', required: true, id: 'faq-answer' },
        slots: { default: () => h(UiTextEditor, { modelValue: 'Texte', preset: 'basic', lang: 'en' }) },
        global: { provide: { [UI_LABELS as symbol]: labels, [ADMIN_THEME as symbol]: { id: 'natheo_atelier' } } },
      })
    );
    expect(wrapper.get('[aria-busy="true"]').text()).toBe('text_editor.loading');
    await flushPromises();
    expect(propsOf(wrapper)).toMatchObject({
      modelValue: 'Texte',
      preset: 'basic',
      keywords: [],
      id: 'faq-answer',
      labelledBy: 'faq-answer-label',
      describedBy: 'faq-answer-error',
      invalid: true,
      required: true,
      rows: 8,
      lang: 'en',
      theme: 'natheo_atelier',
    });
    expect(wrapper.get('label').attributes('id')).toBe('faq-answer-label');
  });

  it('transmet la valeur émise par l’éditeur', async () => {
    const wrapper = mount(UiTextEditor, withLabels({ props: { modelValue: null, label: 'Texte' } }));
    await flushPromises();
    expect(propsOf(wrapper)).toMatchObject({ modelValue: '', label: 'Texte', theme: 'natheo_studio', invalid: false });
    await wrapper.get('.fake-editor').trigger('click');
    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['modifié']);
  });

  it('donne le focus à l’éditeur au clic sur le libellé', async () => {
    const wrapper = mount(
      UiField,
      withLabels({
        props: { label: 'Réponse', id: 'faq-answer' },
        slots: { default: () => h(UiTextEditor) },
        attachTo: document.body,
      })
    );
    await flushPromises();
    await wrapper.get('label').trigger('click');
    expect(document.activeElement?.id).toBe('faq-answer');
    wrapper.unmount();
  });

  it('garde une zone de texte Markdown si l’éditeur ne se charge pas', async () => {
    vi.spyOn(console, 'error').mockImplementation(() => undefined);
    vi.mocked(loadAdminEditor).mockRejectedValue(new Error('réseau'));
    const wrapper = mount(UiTextEditor, withLabels({ props: { modelValue: '**Gras**', label: 'Texte' } }));
    await flushPromises();
    await nextTick();
    expect(wrapper.text()).toContain('text_editor.load_error');
    const textarea = wrapper.get('textarea');
    expect((textarea.element as HTMLTextAreaElement).value).toBe('**Gras**');
    expect(textarea.attributes('aria-label')).toBe('Texte');
    await textarea.setValue('*Italique*');
    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['*Italique*']);
  });
});
