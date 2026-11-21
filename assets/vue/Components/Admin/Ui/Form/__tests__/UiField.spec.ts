import { describe, expect, it } from 'vitest';
import { h, nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import UiField from '@/vue/Components/Admin/Ui/Form/UiField.vue';
import UiInput from '@/vue/Components/Admin/Ui/Form/UiInput.vue';
import UiTextarea from '@/vue/Components/Admin/Ui/Form/UiTextarea.vue';
import UiCheckbox from '@/vue/Components/Admin/Ui/Form/UiCheckbox.vue';
import { withLabels } from '../../__tests__/support';

describe('UiField', () => {
  it("relie le libellé, l'aide et l'erreur au champ", () => {
    const wrapper = mount(
      UiField,
      withLabels({
        props: { label: 'Libellé', help: 'Visible sur le site', error: 'Obligatoire', required: true, id: 'tag-label' },
        slots: { default: () => h(UiInput) },
      })
    );
    const input = wrapper.get('input');
    expect(wrapper.get('label').attributes('for')).toBe('tag-label');
    expect(input.attributes('id')).toBe('tag-label');
    expect(input.attributes('aria-describedby')).toBe('tag-label-error tag-label-help');
    expect(input.attributes('aria-invalid')).toBe('true');
    expect(input.attributes('required')).toBeDefined();
    expect(wrapper.text()).toContain('field.required');
  });

  it('place le contenu de below sous le contrôle, avec son propre identifiant', () => {
    const wrapper = mount(
      UiField,
      withLabels({
        props: { label: 'Libellé', required: true, id: 'tag-label' },
        slots: { default: () => h(UiInput), below: () => h(UiCheckbox, { label: 'Recopier' }) },
      })
    );
    const checkbox = wrapper.get('input[type="checkbox"]');
    expect(checkbox.attributes('id')).not.toBe('tag-label');
    expect(checkbox.attributes('required')).toBeUndefined();
    expect(wrapper.get('input[type="text"]').attributes('id')).toBe('tag-label');
  });

  it("n'a ni aide ni erreur par défaut", () => {
    const wrapper = mount(UiField, withLabels({ props: { label: 'Nom' }, slots: { default: () => h(UiInput) } }));
    const input = wrapper.get('input');
    expect(input.attributes('aria-describedby')).toBeUndefined();
    expect(input.attributes('aria-invalid')).toBeUndefined();
  });

  it('affiche le compteur du champ sur la ligne des erreurs', async () => {
    const wrapper = mount(
      UiField,
      withLabels({
        props: { label: 'Libellé', error: 'Obligatoire' },
        slots: { default: () => h(UiInput, { modelValue: 'Vue', maxlength: 40, counter: true }) },
      })
    );
    await nextTick();
    // Erreurs dans la colonne de gauche de la ligne, compteur à droite
    const row = wrapper.get('[id$="-error"]').element.parentElement?.parentElement;
    expect(row?.textContent).toContain('Obligatoire');
    expect(row?.textContent).toContain('input.counter(3,40)');
    expect(wrapper.text().match(/input\.counter/g)).toHaveLength(1);
  });
});

describe('UiInput', () => {
  it('suit son v-model et compte les caractères', async () => {
    const wrapper = mount(
      UiInput,
      withLabels({ props: { modelValue: 'Vue', maxlength: 20, counter: true, 'onUpdate:modelValue': () => {} } })
    );
    expect(wrapper.text()).toContain('input.counter(3,20)');
    await wrapper.get('input').setValue('Symfony');
    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['Symfony']);
  });
});

describe('UiTextarea', () => {
  it('transmet sa valeur', async () => {
    const wrapper = mount(UiTextarea, withLabels());
    await wrapper.get('textarea').setValue('Texte');
    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['Texte']);
  });
});
