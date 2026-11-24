import { describe, expect, it } from 'vitest';
import { command, coreEditor, rewrite } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module bold', () => {
  it('garde la marque et la pose sur la sélection', () => {
    expect(rewrite('Un **gras** texte')).toBe('Un **gras** texte');
    const editor = coreEditor('Texte');
    editor.commands.setTextSelection({ from: 1, to: 6 });
    command('bold').run?.(editor);
    expect(editor.getMarkdown()).toBe('**Texte**');
    expect(command('bold').isActive?.(editor)).toBe(true);
  });
});
