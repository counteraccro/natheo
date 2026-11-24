import { afterEach, describe, expect, it } from 'vitest';
import { nextTick } from 'vue';
import { editableAttributes } from '@/vue/Composables/Admin/Editor/NatheoEditor/useNatheoEditor';
import { editorProps, mountEditor } from '@tests/vue/Composables/Admin/Editor/NatheoEditor/support';

/** Collage simulé : happy-dom ne fournit pas de DataTransfer rempli */
const paste = (target: Element, data: Record<string, string>): void => {
  const event = new Event('paste', { bubbles: true, cancelable: true }) as ClipboardEvent;
  Object.defineProperty(event, 'clipboardData', {
    value: { getData: (type: string) => data[type] ?? '', types: Object.keys(data) },
  });
  target.dispatchEvent(event);
};

describe('useNatheoEditor', () => {
  let unmount: () => void = () => undefined;
  afterEach(() => unmount());

  it('affiche le Markdown reçu et compte les mots', async () => {
    const { wrapper, state } = await mountEditor({ modelValue: 'Un **texte** court' });
    unmount = () => wrapper.unmount();
    expect(wrapper.get('.ProseMirror strong').text()).toBe('texte');
    expect(state.words.value).toBe(3);
  });

  it('émet le texte reçu tel quel tant que le document est identique', async () => {
    const { wrapper, emitted, editor } = await mountEditor({ modelValue: 'Un __texte__ court' });
    unmount = () => wrapper.unmount();
    editor().commands.insertContentAt(1, 'X');
    expect(emitted[emitted.length - 1]).toBe('XUn **texte** court');
    editor().commands.undo();
    expect(emitted[emitted.length - 1]).toBe('Un __texte__ court');
  });

  it('relit une valeur remplacée par le formulaire sans l’émettre ni la garder dans l’historique', async () => {
    const { wrapper, props, emitted, editor, state } = await mountEditor({ modelValue: 'Avant' });
    unmount = () => wrapper.unmount();
    editor().commands.insertContentAt(1, 'X');
    props.modelValue = 'Après *rechargement*';
    await nextTick();
    expect(wrapper.get('.ProseMirror em').text()).toBe('rechargement');
    expect(emitted).toEqual(['XAvant']);
    expect(editor().can().undo()).toBe(false);
    expect(state.words.value).toBe(2);
  });

  it('passe en lecture seule et suit les attributs du champ', async () => {
    const { wrapper, props, state } = await mountEditor({ modelValue: 'Texte', labelledBy: 'champ-label' });
    unmount = () => wrapper.unmount();
    const zone = wrapper.get('.ProseMirror');
    expect(zone.attributes()).toMatchObject({ id: 'champ', 'aria-labelledby': 'champ-label', contenteditable: 'true' });
    props.readonly = true;
    props.invalid = true;
    props.lang = 'en';
    await nextTick();
    expect(state.editable.value).toBe(false);
    expect(zone.attributes()).toMatchObject({
      contenteditable: 'false',
      'aria-invalid': 'true',
      'aria-readonly': 'true',
      lang: 'en',
    });
  });

  it('donne la hauteur en lignes et un maximum de 60 vh', async () => {
    const { wrapper, state } = await mountEditor({ rows: 4 });
    unmount = () => wrapper.unmount();
    expect(state.height.value).toEqual({ minHeight: '6.6em', maxHeight: '60vh' });
  });

  it('lit un texte collé sans HTML comme du Markdown, sauf dans un bloc de code', async () => {
    const { wrapper, editor, emitted } = await mountEditor({ modelValue: '' });
    unmount = () => wrapper.unmount();
    editor().commands.focus();
    paste(wrapper.get('.ProseMirror').element, { 'text/plain': 'Du **gras**' });
    expect(emitted[emitted.length - 1]).toBe('Du **gras**');
    expect(wrapper.find('.ProseMirror strong').exists()).toBe(true);

    editor().commands.setContent('```\ncode\n```', { contentType: 'markdown' });
    editor().commands.setTextSelection(3);
    paste(wrapper.get('.ProseMirror').element, { 'text/plain': '**pas du gras**' });
    expect(wrapper.find('pre strong').exists()).toBe(false);
  });

  it('nomme la zone par aria-label hors d’un champ et n’écrit pas les attributs vides', () => {
    expect(editableAttributes(editorProps({ label: 'Texte' }))).toEqual({
      id: 'champ',
      'aria-label': 'Texte',
      'aria-multiline': 'true',
      class: 'natheo-editor-content',
    });
  });
});
