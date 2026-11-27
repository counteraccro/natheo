import { describe, expect, it } from 'vitest';
import { command, coreEditor, rewrite } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module keywords', () => {
  it('garde les mots-clés sans les échapper, même sans liste de mots-clés et dans un lien', () => {
    expect(rewrite('Bonjour [[user.login]],')).toBe('Bonjour [[user.login]],');
    expect(rewrite('Site [[[global.site_name]]]([[global.url_change_psw]]).')).toBe(
      'Site [[[global.site_name]]]([[global.url_change_psw]]).'
    );
    expect(rewrite('**[[url.time]] minutes**')).toBe('**[[url.time]] minutes**');
  });

  it('montre les mots-clés en pastille sans changer le texte', () => {
    const editor = coreEditor('Bonjour [[user.login]] et `[[code]]`');
    const pills = editor.view.dom.querySelectorAll('.natheo-editor-keyword');
    expect(Array.from(pills).map((pill) => pill.textContent)).toEqual(['[[user.login]]']);
  });

  it('crée une commande par mot-clé donné par le champ', () => {
    const keywords = [{ keyword: '[[user.login]]', label: 'Identifiant' }];
    const insert = command('keyword:user.login', { keywords });
    expect(insert).toMatchObject({ label: 'Identifiant', hint: '[[user.login]]', translated: true, group: 'keyword' });
    const editor = coreEditor('Bonjour ');
    editor.commands.setTextSelection(9);
    insert.run?.(editor);
    expect(rewrite(editor.getMarkdown())).toBe('Bonjour [[user.login]]');
  });
});
