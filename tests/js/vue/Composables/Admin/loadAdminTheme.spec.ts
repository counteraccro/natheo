import { describe, expect, it } from 'vitest';
import { loadAdminTheme, themeFolder, themeLoaders } from '@/vue/Composables/Admin/loadAdminTheme';
import type { AdminTheme } from '@/ts/Admin/Theme/AdminTheme.type';

const theme = (id: string): AdminTheme => ({
  id,
  layout: {},
  notFound: {},
  screens: { collection: {}, form: {} },
});

const core = {
  './vue/Components/Admin/Themes/NatheoStudio/theme.ts': async () => theme('natheo_studio'),
  './vue/Components/Admin/Themes/NatheoAtelier/theme.ts': async () => theme('natheo_atelier'),
};

describe('loadAdminTheme', () => {
  it('déduit le dossier de l’identifiant', () => {
    expect(themeFolder('natheo_studio')).toBe('NatheoStudio');
    expect(themeFolder('mon_theme_maison')).toBe('MonThemeMaison');
  });

  it('charge le thème demandé', async () => {
    expect((await loadAdminTheme('natheo_atelier', themeLoaders(core))).id).toBe('natheo_atelier');
  });

  it('se replie sur Natheo-Studio pour un identifiant inconnu', async () => {
    expect((await loadAdminTheme('inconnu', themeLoaders(core))).id).toBe('natheo_studio');
  });

  it('préfère le thème de la surcharge et accepte un nouveau thème', async () => {
    const loaders = themeLoaders(core, {
      './overwrite/vue/Components/Admin/Themes/NatheoStudio/theme.ts': async () => ({
        ...theme('natheo_studio'),
        layout: { name: 'surcharge' },
      }),
      './overwrite/vue/Components/Admin/Themes/MonTheme/theme.ts': async () => theme('mon_theme'),
    });
    expect((await loadAdminTheme('natheo_studio', loaders)).layout).toEqual({ name: 'surcharge' });
    expect((await loadAdminTheme('mon_theme', loaders)).id).toBe('mon_theme');
  });

  it('échoue sans aucun thème', async () => {
    await expect(loadAdminTheme('natheo_studio', {})).rejects.toThrow();
  });
});
