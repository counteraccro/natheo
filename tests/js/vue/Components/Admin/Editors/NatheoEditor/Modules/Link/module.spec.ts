import { describe, expect, it } from 'vitest';
import { command, coreEditor, rewrite } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module link', () => {
  it('garde les liens autorisés, P#id et mots-clés compris, sans cible ajoutée', () => {
    const markdown = '[site](https://natheo.fr "Titre") [page](P#12) [mail](mailto:a@b.fr) [x]([[global.url]])';
    expect(rewrite(markdown)).toBe(markdown);
    const editor = coreEditor('[page](P#12)');
    expect(editor.getJSON().content?.[0]?.content?.[0]?.marks?.[0]?.attrs).toMatchObject({
      href: 'P#12',
      target: null,
    });
  });

  it('garde un lien refusé du texte reçu sans le mettre dans la zone de saisie', () => {
    const editor = coreEditor('[x](javascript:alert(1))');
    expect(editor.getMarkdown()).toBe('[x](javascript:alert(1))');
    expect(editor.getHTML()).not.toContain('javascript:');
  });

  it('ouvre la fenêtre du module', () => {
    expect(command('link').run).toBeUndefined();
    expect(command('link').shortcut).toBe('Mod-k');
  });
});
