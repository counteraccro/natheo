import { describe, expect, it } from 'vitest';
import { command, coreEditor } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module clearFormatting', () => {
  it('retire la mise en forme de la sélection et garde les liens', () => {
    const editor = coreEditor('Un **gras** et *italique* ~~barré~~ `code` vers [le site](https://natheo.fr)');
    editor.commands.selectAll();
    expect(command('clearFormatting').isEnabled?.(editor)).toBe(true);
    command('clearFormatting').run?.(editor);
    expect(editor.getMarkdown()).toBe('Un gras et italique barré code vers [le site](https://natheo.fr)');
    expect(command('clearFormatting').isEnabled?.(editor)).toBe(false);
  });

  it('garde les titres et les listes', () => {
    const editor = coreEditor('## Un **titre**\n\n- un *point*');
    editor.commands.selectAll();
    command('clearFormatting').run?.(editor);
    expect(editor.getMarkdown()).toBe('## Un titre\n\n- un point');
  });

  it('est désactivé sur un texte sans mise en forme', () => {
    const editor = coreEditor('Texte [lien](https://natheo.fr)');
    editor.commands.selectAll();
    expect(command('clearFormatting').isEnabled?.(editor)).toBe(false);
  });

  it('retire la marque du mot sous le curseur', () => {
    const editor = coreEditor('Un **gras** mot');
    editor.commands.setTextSelection(6);
    command('clearFormatting').run?.(editor);
    expect(editor.getMarkdown()).toBe('Un gras mot');
  });
});
