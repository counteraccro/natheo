import { describe, expect, it } from 'vitest';
import { command, coreEditor } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module table', () => {
  it('garde le contenu et l’alignement d’un tableau', () => {
    const editor = coreEditor('| A | B |\n|---|:-:|\n| 1 | 2 |');
    expect(editor.getMarkdown().trim()).toBe('| A   | B   |\n| --- | :---: |\n| 1   | 2   |');
  });

  it('insère un tableau puis montre la sous-barre dans celui-ci', () => {
    const editor = coreEditor('Texte');
    expect(command('tableAddRow').visible?.(editor)).toBe(false);
    command('table').run?.(editor);
    expect(command('tableAddRow').visible?.(editor)).toBe(true);
    expect(command('table').isEnabled?.(editor)).toBe(false);
    command('tableAddRow').run?.(editor);
    command('tableAddColumn').run?.(editor);
    const rows = editor
      .getMarkdown()
      .trim()
      .split('\n')
      .filter((line) => line.startsWith('|'));
    expect(rows).toHaveLength(5);
    expect(rows[0].split('|')).toHaveLength(6);
    command('tableDelete').run?.(editor);
    expect(editor.getMarkdown()).not.toContain('|');
  });
});
