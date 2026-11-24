import { describe, expect, it } from 'vitest';
import { coreEditor, rewrite } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module document', () => {
  it('garde les paragraphes et écrit le saut forcé avec \\', () => {
    expect(rewrite('Un\n\nDeux  \nsuite')).toBe('Un\n\nDeux\\\nsuite');
    expect(rewrite('Ligne\\\nforcée')).toBe('Ligne\\\nforcée');
  });

  it('lit comme le serveur un saut de ligne simple, une adresse nue et une entité', () => {
    expect(rewrite('un\ndeux')).toBe('un deux');
    expect(rewrite('Voir https://natheo.fr ou contact@natheo.fr')).toBe('Voir https://natheo.fr ou contact@natheo.fr');
    expect(coreEditor('a&nbsp;b &copy;').getText()).toBe('a\u00a0b ©');
  });

  it('écrit un paragraphe vide &nbsp;, sauf en début et en fin de texte', () => {
    expect(rewrite('Un\n\n&nbsp;\n\nDeux')).toBe('Un\n\n&nbsp;\n\nDeux');
    const editor = coreEditor('Un');
    editor.commands.setTextSelection(3);
    editor.commands.splitBlock();
    expect(rewrite(editor.getMarkdown())).toBe('Un');
  });

  it('garde une image seule dans son paragraphe', () => {
    expect(coreEditor('![a](/a.png)').getJSON().content?.[0]).toMatchObject({ type: 'paragraph' });
  });

  it('est chargé quel que soit le préréglage', () => {
    expect(coreEditor('Texte', { preset: [] }).getMarkdown()).toBe('Texte');
  });
});
