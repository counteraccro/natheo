import { describe, expect, it } from 'vitest';
import { coreEditor, rewrite } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module document', () => {
  it('garde les paragraphes et écrit le saut forcé avec \\', () => {
    expect(rewrite('Un\n\nDeux  \nsuite')).toBe('Un\n\nDeux\\\nsuite');
    expect(rewrite('Ligne\\\nforcée')).toBe('Ligne\\\nforcée');
  });

  it('est chargé quel que soit le préréglage', () => {
    expect(coreEditor('Texte', { preset: [] }).getMarkdown()).toBe('Texte');
  });
});
