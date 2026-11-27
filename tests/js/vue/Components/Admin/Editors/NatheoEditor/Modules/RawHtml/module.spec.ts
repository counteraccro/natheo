import { describe, expect, it } from 'vitest';
import { coreEditor, rewrite } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module rawHtml', () => {
  it('rend à l’identique un bloc HTML, une balise et un commentaire dans le texte', () => {
    const markdown = '<div class="x">\nhtml\n</div>\n\nTexte <span class="a">dedans</span> fin <!-- note -->';
    expect(rewrite(markdown)).toBe(markdown);
    expect(coreEditor(markdown).getJSON().content?.[0]).toMatchObject({ type: 'rawHtml' });
  });

  it('garde les attributs de league/commonmark', () => {
    expect(rewrite('Paragraphe {.classe #id data-x="1"}')).toBe('Paragraphe {.classe #id data-x="1"}');
    expect(rewrite('Total {count}')).toBe('Total {count}');
  });

  it('ne crée jamais de bloc HTML depuis du HTML collé', () => {
    const editor = coreEditor('');
    editor.commands.insertContent('<pre class="natheo-editor-raw" data-raw="block"><script>x</script></pre>');
    expect(JSON.stringify(editor.getJSON())).not.toContain('rawHtml');
  });
});
