import { describe, expect, it } from 'vitest';
import { command, coreEditor, rewrite } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module list', () => {
  it('garde les listes imbriquées et numérotées, puces écrites avec -', () => {
    expect(rewrite('- a\n- b\n  - b1\n\n3. trois\n4. quatre')).toBe('- a\n- b\n  - b1\n\n3. trois\n4. quatre');
    expect(rewrite('* a\n* b')).toBe('- a\n- b');
  });

  it('change un paragraphe en liste', () => {
    const editor = coreEditor('Texte');
    command('bulletList').run?.(editor);
    expect(editor.getMarkdown()).toBe('- Texte');
    command('orderedList').run?.(editor);
    expect(editor.getMarkdown()).toBe('1. Texte');
  });
});
