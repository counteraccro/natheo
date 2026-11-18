import { afterEach, beforeAll, describe, expect, it } from 'vitest';
import { defineComponent, h, nextTick, ref } from 'vue';
import { mount } from '@vue/test-utils';
import UiDialog from '@/vue/Components/Admin/Ui/Overlay/UiDialog.vue';
import UiConfirmDialog from '@/vue/Components/Admin/Ui/Overlay/UiConfirmDialog.vue';
import { withLabels } from '../../__tests__/support';

beforeAll(() => {
  // Comportement natif simulé : happy-dom ne gère pas tout <dialog>
  const proto = HTMLDialogElement.prototype;
  proto.showModal = function (this: HTMLDialogElement) {
    this.setAttribute('open', '');
  };
  proto.close = function (this: HTMLDialogElement) {
    if (!this.hasAttribute('open')) return;
    this.removeAttribute('open');
    this.dispatchEvent(new Event('close'));
  };
});

afterEach(() => {
  document.body.innerHTML = '';
});

const Host = defineComponent({
  setup() {
    const open = ref(false);
    return () => [
      h('button', { id: 'opener', onClick: () => (open.value = true) }, 'Ouvrir'),
      h(UiDialog, { open: open.value, 'onUpdate:open': (v: boolean) => (open.value = v), title: 'Titre' }, () =>
        h('p', 'Contenu')
      ),
    ];
  },
});

describe('UiDialog', () => {
  it("s'ouvre, relie son titre et rend le focus à l'élément d'origine", async () => {
    const wrapper = mount(Host, withLabels({ attachTo: document.body }));
    const opener = wrapper.get('#opener');
    (opener.element as HTMLElement).focus();
    await opener.trigger('click');
    await nextTick();
    const dialog = wrapper.get('dialog');
    expect(dialog.attributes('open')).toBeDefined();
    expect(dialog.attributes('aria-labelledby')).toBe(wrapper.get('h2').attributes('id'));
    await wrapper.get('[aria-label="dialog.close"]').trigger('click');
    await nextTick();
    expect(dialog.attributes('open')).toBeUndefined();
    expect(document.activeElement).toBe(opener.element);
  });

  it('se ferme sur Échap (événement close du navigateur)', async () => {
    const wrapper = mount(UiDialog, withLabels({ props: { open: true, title: 'Titre' }, attachTo: document.body }));
    await nextTick();
    wrapper.get('dialog').element.dispatchEvent(new Event('close'));
    expect(wrapper.emitted('update:open')?.[0]).toEqual([false]);
  });
});

describe('UiConfirmDialog', () => {
  it('place Annuler puis Confirmer, et donne le focus à Annuler pour une suppression', async () => {
    const wrapper = mount(
      UiConfirmDialog,
      withLabels({ props: { open: true, title: 'Supprimer le tag', tone: 'danger' }, attachTo: document.body })
    );
    await nextTick();
    const buttons = wrapper.get('footer').findAll('button');
    expect(buttons.map((b) => b.text())).toEqual(['confirm_dialog.cancel', 'confirm_dialog.confirm']);
    expect(buttons[0].attributes('autofocus')).toBeDefined();
    expect(buttons[1].attributes('autofocus')).toBeUndefined();
    expect(wrapper.get('dialog').attributes('data-tone')).toBe('danger');
  });

  it('émet confirm sans se fermer, cancel en se fermant', async () => {
    const wrapper = mount(
      UiConfirmDialog,
      withLabels({ props: { open: true, title: 'Valider' }, attachTo: document.body })
    );
    await nextTick();
    const [cancel, confirm] = wrapper.get('footer').findAll('button');
    await confirm.trigger('click');
    expect(wrapper.emitted('confirm')).toHaveLength(1);
    expect(wrapper.emitted('update:open')).toBeUndefined();
    await cancel.trigger('click');
    expect(wrapper.emitted('cancel')).toHaveLength(1);
    expect(wrapper.emitted('update:open')?.[0]).toEqual([false]);
  });
});
