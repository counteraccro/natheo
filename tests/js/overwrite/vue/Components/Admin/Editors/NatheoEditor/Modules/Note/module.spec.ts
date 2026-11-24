import { describe, expect, it } from 'vitest';
import { command, coreEditor, rewrite } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module note de la surcharge', () => {
  it('lit une citation qui commence par **Note** et la réécrit à l’identique', () => {
    const markdown = '> **Note**\n>\n> Premier paragraphe.\n>\n> - un point\n\nSuite';
    expect(rewrite(markdown)).toBe(markdown);
    expect(coreEditor(markdown).getJSON().content?.[0]).toMatchObject({ type: 'note' });
  });

  it('laisse une citation ordinaire en citation', () => {
    expect(coreEditor('> Citation').getJSON().content?.[0]).toMatchObject({ type: 'blockquote' });
  });

  it('encadre le paragraphe courant et le retire', () => {
    const editor = coreEditor('Texte');
    command('note').run?.(editor);
    expect(editor.getMarkdown()).toBe('> **Note**\n>\n> Texte');
    expect(command('note').isActive?.(editor)).toBe(true);
    command('note').run?.(editor);
    expect(editor.getMarkdown()).toBe('Texte');
  });
});
