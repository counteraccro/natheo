import { describe, expect, it } from 'vitest';
import { command, rewrite } from '@tests/vue/Components/Admin/Editors/NatheoEditor/Modules/support';

describe('module image', () => {
  it('garde une image seule ou dans le texte', () => {
    expect(rewrite('![Logo](/assets/logo.png "Titre")')).toBe('![Logo](/assets/logo.png "Titre")');
    expect(rewrite('Avant ![a](/a.png) après')).toBe('Avant ![a](/a.png) après');
  });

  it('ouvre la fenêtre du module', () => {
    expect(command('image').run).toBeUndefined();
  });
});
