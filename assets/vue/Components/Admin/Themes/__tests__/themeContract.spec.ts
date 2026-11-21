import { describe, expect, it } from 'vitest';
import type { AdminTheme } from '@/ts/Admin/Theme/AdminTheme.type';

const themes = import.meta.glob<AdminTheme>('../*/theme.ts', { eager: true, import: 'default' });

describe('Thèmes du back-office', () => {
  it('trouve au moins le thème par défaut', () => {
    expect(Object.values(themes).map((theme) => theme.id)).toContain('natheo_studio');
  });

  it.each(Object.entries(themes))('%s fournit les éléments du contrat', (_file, theme) => {
    expect(theme.id).toMatch(/^[a-z][a-z0-9_]*$/);
    expect(theme.layout).toBeTruthy();
    expect(typeof theme.layout).toBe('object');
    expect(theme.notFound).toBeTruthy();
    expect(theme.screens.collection).toBeTruthy();
    expect(theme.screens.form).toBeTruthy();
  });

  it('donne à chaque thème un identifiant unique', () => {
    const ids = Object.values(themes).map((theme) => theme.id);
    expect(new Set(ids).size).toBe(ids.length);
  });
});
