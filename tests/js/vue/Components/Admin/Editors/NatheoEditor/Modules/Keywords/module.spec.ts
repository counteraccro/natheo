import { describe, expect, it } from 'vitest';
import { command, coreEditor, rewrite } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module keywords', () => {
  it('garde les mots-clés sans les échapper, même sans liste de mots-clés', () => {
    expect(rewrite('Bonjour [[user.login]],')).toBe('Bonjour [[user.login]],');
    expect(coreEditor('[[a]] et [[b.c]]', { preset: 'basic' }).getMarkdown()).toBe('[[a]] et [[b.c]]');
  });

  it('crée une commande par mot-clé donné par le champ', () => {
    const keywords = [{ keyword: '[[user.login]]', label: 'Identifiant' }];
    const insert = command('keyword:user.login', { keywords });
    expect(insert).toMatchObject({ label: 'Identifiant', hint: '[[user.login]]', translated: true, group: 'keyword' });
    const editor = coreEditor('Bonjour ');
    editor.commands.setTextSelection(9);
    insert.run?.(editor);
    expect(editor.getMarkdown()).toBe('Bonjour [[user.login]]');
  });

  it('change un mot-clé tapé en pastille', () => {
    const editor = coreEditor('');
    editor.commands.insertContent('[[user.login]');
    const { from, to } = editor.state.selection;
    editor.view.someProp('handleTextInput', (handle) => handle(editor.view, from, to, ']', () => editor.state.tr));
    expect(editor.getJSON().content?.[0]?.content?.[0]).toMatchObject({
      type: 'keyword',
      attrs: { name: 'user.login' },
    });
    expect(editor.getMarkdown()).toBe('[[user.login]]');
  });
});
