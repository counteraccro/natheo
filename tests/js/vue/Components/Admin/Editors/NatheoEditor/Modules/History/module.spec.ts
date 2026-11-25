import { describe, expect, it } from 'vitest';
import { command, coreEditor } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module history', () => {
  it('annule et rétablit', () => {
    const editor = coreEditor('Texte');
    editor.commands.insertContentAt(1, 'X');
    expect(command('undo').isEnabled?.(editor)).toBe(true);
    command('undo').run?.(editor);
    expect(editor.getMarkdown()).toBe('Texte');
    expect(command('redo').isEnabled?.(editor)).toBe(true);
    command('redo').run?.(editor);
    expect(editor.getMarkdown()).toBe('XTexte');
  });
});
