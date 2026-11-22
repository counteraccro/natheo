import { afterEach, describe, expect, it } from 'vitest';
import { defineComponent, h, nextTick, ref } from 'vue';
import { mount } from '@vue/test-utils';
import UiForm from '@/vue/Components/Admin/Ui/Form/UiForm.vue';
import UiErrorSummary from '@/vue/Components/Admin/Ui/Form/UiErrorSummary.vue';
import UiField from '@/vue/Components/Admin/Ui/Form/UiField.vue';
import UiInput from '@/vue/Components/Admin/Ui/Form/UiInput.vue';
import UiTabs from '@/vue/Components/Admin/Ui/Display/UiTabs.vue';
import { useFormErrors } from '@/vue/Composables/Admin/Ui/useFormErrors';
import { withLabels } from '@tests/vue/Components/Admin/Ui/support';

afterEach(() => {
  document.body.innerHTML = '';
});

const TagForm = defineComponent({
  setup() {
    const errors = useFormErrors();
    const tab = ref('fr');
    const submitted = ref(0);
    const field = (name: string, label: string) => () => h(UiField, { name, label }, () => h(UiInput));
    return { errors, tab, submitted, field };
  },
  render() {
    return h(UiForm, { errors: this.errors, onSubmit: () => this.submitted++ }, () => [
      h(UiErrorSummary),
      h(
        UiTabs,
        {
          tabs: [
            { id: 'fr', label: 'Français' },
            { id: 'en', label: 'English' },
          ],
          label: 'Langues',
          modelValue: this.tab,
          'onUpdate:modelValue': (v: string) => (this.tab = v),
          errorPrefixes: { fr: 'translations[fr]', en: 'translations[en]' },
        },
        {
          fr: this.field('translations[fr].label', 'Libellé (fr)'),
          en: this.field('translations[en].label', 'Libellé (en)'),
        }
      ),
      h(UiField, { name: 'color', label: 'Couleur' }, () => h(UiInput)),
      h('button', { type: 'submit' }, 'Enregistrer'),
    ]);
  },
});

const mountForm = () => mount(TagForm, withLabels({ attachTo: document.body }));

describe('UiForm', () => {
  it('désactive la validation du navigateur et émet submit', async () => {
    const wrapper = mountForm();
    expect(wrapper.get('form').attributes('novalidate')).toBeDefined();
    await wrapper.get('form').trigger('submit');
    expect(wrapper.vm.submitted).toBe(1);
  });

  it('affiche les erreurs sur les champs et les efface à la saisie', async () => {
    const wrapper = mountForm();
    wrapper.vm.errors.set([
      { propertyPath: 'color', message: 'Couleur invalide' },
      { propertyPath: 'color', message: 'Format #rrggbb' },
    ]);
    await nextTick();
    const input = wrapper.get('#ui-field-color');
    expect(input.attributes('aria-invalid')).toBe('true');
    expect(input.attributes('aria-describedby')).toBe('ui-field-color-error');
    expect(wrapper.get('#ui-field-color-error').findAll('p')).toHaveLength(2);
    await input.setValue('#123456');
    expect(wrapper.find('#ui-field-color-error').exists()).toBe(false);
    expect(wrapper.vm.errors.count.value).toBe(0);
  });

  it('marque l’onglet en erreur, donne le focus au récapitulatif et ouvre le champ depuis son lien', async () => {
    const wrapper = mountForm();
    wrapper.vm.errors.set([
      { propertyPath: 'translations[en].label', message: 'Obligatoire' },
      { propertyPath: '', message: 'Ce tag existe déjà' },
    ]);
    await nextTick();
    await nextTick();
    const summary = wrapper.get('[role="alert"]');
    expect(document.activeElement).toBe(summary.element);
    expect(summary.text()).toContain('error_summary.title(2)');
    expect(summary.text()).toContain('Ce tag existe déjà');
    const englishTab = wrapper.findAll('[role="tab"]')[1];
    expect(englishTab.text()).toContain('tabs.has_errors');

    const link = summary.get('a');
    expect(link.text()).toBe('error_summary.item(Libellé (en),Obligatoire)');
    await link.trigger('click');
    await nextTick();
    await nextTick();
    expect(englishTab.attributes('aria-selected')).toBe('true');
    expect(document.activeElement?.id).toBe('ui-field-translations-en-label');
    expect(summary.text()).toContain('error_summary.item(Libellé (en),Obligatoire)');
  });
});
