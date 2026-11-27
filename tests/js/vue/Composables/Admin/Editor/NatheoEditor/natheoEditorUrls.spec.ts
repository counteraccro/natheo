import { describe, expect, it } from 'vitest';
import { isAllowedImage, isAllowedLink } from '@/vue/Composables/Admin/Editor/NatheoEditor/natheoEditorUrls';

describe('natheoEditorUrls', () => {
  it.each([
    'https://natheo.fr/page',
    'http://exemple.com',
    'mailto:contact@exemple.com',
    'tel:+33 1 23 45 67 89',
    '/fr/page/accueil',
    'P#12',
    '[[global.url_change_psw]]',
  ])('accepte le lien %s', (url) => expect(isAllowedLink(url)).toBe(true));

  it.each(['javascript:alert(1)', 'data:text/html,x', '//evil.com', 'vbscript:x', 'ftp://x', 'P#abc', ''])(
    'refuse le lien %s',
    (url) => expect(isAllowedLink(url)).toBe(false)
  );

  it('accepte les images web et du site, refuse data:', () => {
    expect(isAllowedImage('https://natheo.fr/a.png')).toBe(true);
    expect(isAllowedImage('/assets/medias/a.png')).toBe(true);
    expect(isAllowedImage('data:image/png;base64,AAA')).toBe(false);
    expect(isAllowedImage('javascript:x')).toBe(false);
  });
});
