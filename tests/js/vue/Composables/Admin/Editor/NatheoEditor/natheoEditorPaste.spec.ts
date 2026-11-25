import { describe, expect, it } from 'vitest';
import { cleanPastedHtml } from '@/vue/Composables/Admin/Editor/NatheoEditor/natheoEditorPaste';

describe('natheoEditorPaste', () => {
  it('retire styles, classes, polices et balises Office', () => {
    const html =
      '<!--StartFragment--><p class="MsoNormal" style="color:red"><span style="font-family:Arial">Texte</span><o:p>&nbsp;</o:p></p><style>p{}</style>';
    expect(cleanPastedHtml(html)).toBe('<p>Texte</p>');
  });

  it('garde le gras et l’italique donnés par un style, retire le faux gras de Google Docs', () => {
    const html =
      '<b style="font-weight:normal" id="docs-internal-guid"><p><span style="font-weight:700">Gras</span> <span style="font-style:italic">penché</span></p></b>';
    expect(cleanPastedHtml(html)).toBe('<p><strong>Gras</strong> <em>penché</em></p>');
  });

  it('retire les liens et images refusés', () => {
    const html =
      '<p><a href="javascript:alert(1)" onclick="x()">lien</a><a href="/fr/page" target="_blank">ok</a><img src="data:image/png;base64,AA"></p>';
    expect(cleanPastedHtml(html)).toBe('<p><a>lien</a><a href="/fr/page">ok</a></p>');
  });

  it('garde les attributs utiles et la langue d’un bloc de code', () => {
    const html =
      '<table><tr><td colspan="2" width="100">a</td></tr></table><ol start="3" type="a"><li>x</li></ol><pre><code class="language-php hljs">echo</code></pre><script>x</script>';
    expect(cleanPastedHtml(html)).toBe(
      '<table><tbody><tr><td colspan="2">a</td></tr></tbody></table><ol start="3"><li>x</li></ol><pre><code class="language-php">echo</code></pre>'
    );
  });

  it('garde telle quelle une copie depuis un éditeur', () => {
    const html = '<p data-pm-slice="1 1 []" style="x">a</p>';
    expect(cleanPastedHtml(html)).toBe(html);
    const fromBrowser = '<meta charset="utf-8"><p data-pm-slice="1 1 []" style="x">a</p>';
    expect(cleanPastedHtml(fromBrowser)).toBe(fromBrowser);
  });

  it('nettoie une page qui contient data-pm-slice ailleurs que sur un élément racine', () => {
    expect(cleanPastedHtml('<p style="x">data-pm-slice</p>')).toBe('<p>data-pm-slice</p>');
    expect(cleanPastedHtml('<div><p data-pm-slice="1 1 []" style="x">a</p></div>')).not.toContain('style');
    expect(cleanPastedHtml('<p data-pm-slice="x" style="x">a</p>')).toBe('<p>a</p>');
  });
});
