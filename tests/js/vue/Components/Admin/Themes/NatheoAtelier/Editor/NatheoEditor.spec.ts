import { afterEach, describe, expect, it } from 'vitest';
import { flushPromises } from '@vue/test-utils';
import AtelierEditor from '@admin/Themes/NatheoAtelier/Editor/NatheoEditor.vue';
import { mountView } from '@tests/vue/Components/Admin/Editors/NatheoEditor/support';

describe('Natheo-Editor dans Natheo-Atelier', () => {
  let unmount: () => void = () => undefined;
  afterEach(() => {
    unmount();
    document.body.innerHTML = '';
  });

  it('montre la barre compacte seulement pendant la saisie', async () => {
    const { wrapper } = await mountView(AtelierEditor, { modelValue: 'texte' });
    unmount = () => wrapper.unmount();
    expect(wrapper.find('[role="toolbar"]').exists()).toBe(false);
    await wrapper.get('.natheo-editor-atelier').trigger('focusin');
    const labels = wrapper.findAll('[role="toolbar"] button').map((button) => button.attributes('aria-label'));
    expect(labels).toEqual([
      'block_menu(command.paragraph)',
      undefined,
      'command.bold',
      'command.italic',
      'command.clear_formatting',
      'command.link',
    ]);
    expect(wrapper.text()).toContain('slash_hint');
  });

  it('ouvre le menu « / » sur une ligne vide et insère la commande choisie', async () => {
    const { wrapper, editor, emitted } = await mountView(AtelierEditor, { modelValue: 'texte' });
    unmount = () => wrapper.unmount();
    editor().commands.setTextSelection(editor().state.doc.content.size);
    editor().commands.splitBlock();
    editor().commands.insertContent('/head');
    await flushPromises();
    const options = document.body.querySelectorAll('[role="option"]');
    expect([...options].map((option) => option.querySelector('b')?.textContent)).toEqual([
      'command.heading_2',
      'command.heading_3',
      'command.heading_4',
    ]);
    editor().view.dom.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowDown', bubbles: true }));
    editor().view.dom.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true }));
    await flushPromises();
    editor().commands.insertContent('Titre');
    expect(emitted[emitted.length - 1]).toBe('texte\n\n### Titre');
    expect(document.body.querySelector('[role="listbox"]')).toBeNull();
  });

  it("ne propose pas le menu « / » au milieu d'un texte", async () => {
    const { wrapper, editor } = await mountView(AtelierEditor, { modelValue: 'texte' });
    unmount = () => wrapper.unmount();
    editor().commands.setTextSelection(editor().state.doc.content.size - 1);
    editor().commands.insertContent(' /');
    await flushPromises();
    expect(document.body.querySelector('[role="listbox"]')).toBeNull();
  });
});
