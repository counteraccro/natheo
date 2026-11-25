import { describe, expect, it } from 'vitest';
import { command, coreEditor, rewrite } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module codeBlock', () => {
  it('garde la langue du bloc et pose un bloc', () => {
    expect(rewrite('```php\necho 1;\n```')).toBe('```php\necho 1;\n```');
    const editor = coreEditor('Texte');
    command('codeBlock').run?.(editor);
    expect(editor.getMarkdown()).toBe('```\nTexte\n```');
  });
});
