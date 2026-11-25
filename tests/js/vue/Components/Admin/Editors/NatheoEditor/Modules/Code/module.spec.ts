import { describe, expect, it } from 'vitest';
import { command, coreEditor, rewrite } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module code', () => {
  it('garde la marque et la pose sur la sélection', () => {
    expect(rewrite('Un \`code\` texte')).toBe('Un \`code\` texte');
    const editor = coreEditor('Texte');
    editor.commands.setTextSelection({ from: 1, to: 6 });
    command('code').run?.(editor);
    expect(editor.getMarkdown()).toBe('\`Texte\`');
    expect(command('code').isActive?.(editor)).toBe(true);
  });
});
