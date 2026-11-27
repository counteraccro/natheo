import { describe, expect, it } from 'vitest';
import { command, coreEditor, rewrite } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module italic', () => {
  it('garde la marque et la pose sur la sélection', () => {
    expect(rewrite('Un *penché* texte')).toBe('Un *penché* texte');
    const editor = coreEditor('Texte');
    editor.commands.setTextSelection({ from: 1, to: 6 });
    command('italic').run?.(editor);
    expect(editor.getMarkdown()).toBe('*Texte*');
    expect(command('italic').isActive?.(editor)).toBe(true);
  });
});
