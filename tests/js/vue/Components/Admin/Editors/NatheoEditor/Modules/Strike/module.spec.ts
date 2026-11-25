import { describe, expect, it } from 'vitest';
import { command, coreEditor, rewrite } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module strike', () => {
  it('garde la marque et la pose sur la sélection', () => {
    expect(rewrite('Un ~~barré~~ texte')).toBe('Un ~~barré~~ texte');
    const editor = coreEditor('Texte');
    editor.commands.setTextSelection({ from: 1, to: 6 });
    command('strike').run?.(editor);
    expect(editor.getMarkdown()).toBe('~~Texte~~');
    expect(command('strike').isActive?.(editor)).toBe(true);
  });
});
