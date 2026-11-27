import { afterEach, describe, expect, it } from 'vitest';
import { nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import LinkDialog from '@admin/Editors/NatheoEditor/Modules/Link/LinkDialog.vue';
import { mountView, translate } from '@tests/vue/Components/Admin/Editors/NatheoEditor/support';
import { withLabels } from '@tests/vue/Components/Admin/Ui/support';
import type { Editor } from '@tiptap/core';

/** happy-dom ne fournit pas showModal */
HTMLDialogElement.prototype.showModal ??= function (this: HTMLDialogElement) {
  this.open = true;
};
HTMLDialogElement.prototype.close ??= function (this: HTMLDialogElement) {
  this.open = false;
  this.dispatchEvent(new Event('close'));
};

const openDialog = (editor: Editor) =>
  mount(LinkDialog, withLabels({ props: { editor, command: 'link', t: translate }, attachTo: document.body }));

describe('LinkDialog', () => {
  let unmount: () => void = () => undefined;
  afterEach(() => unmount());

  it('pose un lien sur la sélection', async () => {
    const { wrapper, editor, emitted } = await mountView(null, { modelValue: 'Un texte' });
    editor().commands.setTextSelection({ from: 4, to: 9 });
    const dialog = openDialog(editor());
    unmount = () => (dialog.unmount(), wrapper.unmount());
    await nextTick();
    const [url, text] = dialog.findAll('input');
    expect((text.element as HTMLInputElement).value).toBe('texte');
    await url.setValue('https://natheo.fr');
    await dialog.get('form').trigger('submit');
    expect(emitted[emitted.length - 1]).toBe('Un [texte](https://natheo.fr)');
    expect(dialog.emitted('close')).toBeTruthy();
  });

  it('refuse une adresse non autorisée', async () => {
    const { wrapper, editor, emitted } = await mountView(null, { modelValue: 'Un texte' });
    const dialog = openDialog(editor());
    unmount = () => (dialog.unmount(), wrapper.unmount());
    await nextTick();
    await dialog.findAll('input')[0].setValue('javascript:alert(1)');
    await dialog.get('form').trigger('submit');
    expect(dialog.text()).toContain('link_dialog.error');
    expect(emitted).toEqual([]);
  });

  it('modifie puis retire un lien existant', async () => {
    const { wrapper, editor, emitted } = await mountView(null, { modelValue: 'Voir [la page](P#12)' });
    editor().commands.setTextSelection(8);
    const dialog = openDialog(editor());
    unmount = () => (dialog.unmount(), wrapper.unmount());
    await nextTick();
    expect((dialog.findAll('input')[0].element as HTMLInputElement).value).toBe('P#12');
    expect(dialog.text()).toContain('link_dialog.title_edit');
    const remove = dialog.findAll('button').find((button) => button.text() === 'link_dialog.remove');
    await remove?.trigger('click');
    expect(emitted[emitted.length - 1]).toBe('Voir la page');
  });
});
