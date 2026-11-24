import { afterEach, describe, expect, it } from 'vitest';
import { nextTick } from 'vue';
import StudioEditor from '@admin/Themes/NatheoStudio/Editor/NatheoEditor.vue';
import { mountView } from '@tests/vue/Components/Admin/Editors/NatheoEditor/support';

describe('Natheo-Editor dans Natheo-Studio', () => {
  let unmount: () => void = () => undefined;
  afterEach(() => unmount());

  it("affiche la barre d'outils et l'état pressé de la mise en forme", async () => {
    const { wrapper, editor } = await mountView(StudioEditor, { modelValue: '**gras** texte' });
    unmount = () => wrapper.unmount();
    expect(wrapper.get('[role="toolbar"]').attributes('aria-label')).toBe('toolbar');
    editor().commands.setTextSelection(2);
    await nextTick();
    expect(wrapper.get('button[aria-label="command.bold"]').attributes('aria-pressed')).toBe('true');
    expect(wrapper.get('button[aria-label="command.italic"]').attributes('aria-pressed')).toBe('false');
  });

  it('lance la commande au clic', async () => {
    const { wrapper, emitted, editor } = await mountView(StudioEditor, { modelValue: 'texte' });
    unmount = () => wrapper.unmount();
    editor().commands.selectAll();
    await wrapper.get('button[aria-label="command.bold"]').trigger('click');
    expect(emitted[emitted.length - 1]).toBe('**texte**');
  });

  it('montre le menu des titres et celui des mots-clés', async () => {
    const { wrapper } = await mountView(StudioEditor, {
      modelValue: 'texte',
      keywords: [{ keyword: '[[user.login]]', label: 'Identifiant' }],
    });
    unmount = () => wrapper.unmount();
    expect(wrapper.find('button[aria-label="block_menu(command.paragraph)"]').exists()).toBe(true);
    expect(wrapper.text()).toContain('keyword_menu');
  });

  it('limite la barre au préréglage basic', async () => {
    const { wrapper } = await mountView(StudioEditor, { modelValue: 'texte', preset: 'basic' });
    unmount = () => wrapper.unmount();
    const labels = wrapper.findAll('[role="toolbar"] button').map((button) => button.attributes('aria-label'));
    expect(labels).toEqual([
      'command.undo',
      'command.redo',
      'command.bold',
      'command.italic',
      'command.bullet_list',
      'command.ordered_list',
      'command.link',
    ]);
  });

  it('montre la sous-barre dans un tableau', async () => {
    const { wrapper, editor } = await mountView(StudioEditor, { modelValue: '| A | B |\n| --- | --- |\n| 1 | 2 |' });
    unmount = () => wrapper.unmount();
    editor().commands.setTextSelection(3);
    await nextTick();
    expect(wrapper.find('[role="toolbar"][aria-label="table_toolbar"]').exists()).toBe(true);
  });

  it('bascule en mode Markdown et compte les mots', async () => {
    const { wrapper, emitted } = await mountView(StudioEditor, { modelValue: 'Deux mots' });
    unmount = () => wrapper.unmount();
    expect(wrapper.text()).toContain('words(2)');
    await wrapper
      .findAll('button')
      .find((button) => button.text() === 'markdown')
      ?.trigger('click');
    const source = wrapper.get('textarea.natheo-editor-source');
    expect((source.element as HTMLTextAreaElement).value).toBe('Deux mots');
    await source.setValue('Trois mots ici');
    expect(emitted[emitted.length - 1]).toBe('Trois mots ici');
    expect(wrapper.text()).toContain('words(3)');
  });

  it('masque la barre et le mode Markdown en lecture seule', async () => {
    const { wrapper } = await mountView(StudioEditor, { modelValue: 'texte', readonly: true });
    unmount = () => wrapper.unmount();
    expect(wrapper.find('[role="toolbar"]').exists()).toBe(false);
    expect(wrapper.text()).not.toContain('markdown');
    expect(wrapper.get('[contenteditable]').attributes('contenteditable')).toBe('false');
  });
});
