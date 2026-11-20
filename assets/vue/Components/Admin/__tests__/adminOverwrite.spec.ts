import { describe, expect, it } from 'vitest';
import { resolveAdminPath } from '../../../../../vite.adminPath.mts';
import UiButton from '@admin/Ui/Form/UiButton.vue';
import OriginalUiButton from '@/vue/Components/Admin/Ui/Form/UiButton.vue';

const dirs = { admin: '/app/admin', overwrite: '/app/overwrite' };

describe('Alias @admin/', () => {
  it('sert le fichier surchargé quand il existe', () => {
    const exists = (file: string) => file === '/app/overwrite/Modules/Tag/TagList.vue';
    expect(resolveAdminPath('Modules/Tag/TagList.vue', exists, dirs)).toBe('/app/overwrite/Modules/Tag/TagList.vue');
  });

  it('essaie les extensions d’un import écrit sans extension', () => {
    const exists = (file: string) => file === '/app/overwrite/Themes/Studio/theme.ts';
    expect(resolveAdminPath('Themes/Studio/theme', exists, dirs)).toBe('/app/overwrite/Themes/Studio/theme.ts');
  });

  it('sert le fichier du back-office sans surcharge', () => {
    expect(resolveAdminPath('Ui/Form/UiButton.vue', () => false, dirs)).toBe('/app/admin/Ui/Form/UiButton.vue');
  });

  it('résout un composant du back-office dans Vitest', () => {
    expect(UiButton).toBe(OriginalUiButton);
  });
});
