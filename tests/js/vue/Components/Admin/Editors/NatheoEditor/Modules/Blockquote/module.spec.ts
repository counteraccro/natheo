import { describe, expect, it } from 'vitest';
import { command, coreEditor, rewrite } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module blockquote', () => {
  it('garde et pose une citation', () => {
    expect(rewrite('> Citation')).toBe('> Citation');
    const editor = coreEditor('Texte');
    command('blockquote').run?.(editor);
    expect(editor.getMarkdown()).toBe('> Texte');
  });
});
