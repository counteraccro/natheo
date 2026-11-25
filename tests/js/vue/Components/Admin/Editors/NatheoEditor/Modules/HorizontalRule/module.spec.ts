import { describe, expect, it } from 'vitest';
import { command, coreEditor, rewrite } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module horizontalRule', () => {
  it('garde et insère un séparateur', () => {
    expect(rewrite('Un\n\n---\n\nDeux')).toBe('Un\n\n---\n\nDeux');
    const editor = coreEditor('Texte');
    editor.commands.setTextSelection(6);
    command('horizontalRule').run?.(editor);
    expect(editor.getMarkdown()).toContain('---');
  });
});
