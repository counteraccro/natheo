import { describe, expect, it } from 'vitest';
import { command, coreEditor, rewrite } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module heading', () => {
  it('lit et garde les titres de 1 à 6', () => {
    const markdown = '# Un\n\n## Deux\n\n#### Quatre\n\n###### Six';
    expect(rewrite(markdown)).toBe(markdown);
  });

  it('propose le paragraphe et les titres 2 à 4', () => {
    const editor = coreEditor('Texte');
    command('heading3').run?.(editor);
    expect(editor.getMarkdown()).toBe('### Texte');
    expect(command('heading3').isActive?.(editor)).toBe(true);
    command('paragraph').run?.(editor);
    expect(editor.getMarkdown()).toBe('Texte');
    expect(() => command('heading5')).toThrow();
  });
});
