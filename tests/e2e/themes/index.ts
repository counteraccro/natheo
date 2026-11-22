/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Gestes de liste du thème du projet Playwright en cours
 */
import { test } from '@playwright/test';
import { natheoAtelier } from './natheo_atelier';
import { natheoStudio } from './natheo_studio';
import type { ThemeScreens } from './theme';

const themes: Record<string, ThemeScreens> = { natheo_studio: natheoStudio, natheo_atelier: natheoAtelier };

/**
 * Thème du projet en cours, Natheo-Studio par défaut
 */
export const currentTheme = (): ThemeScreens => themes[test.info().project.name] ?? natheoStudio;

/**
 * Libellé propre au projet : les deux thèmes jouent les mêmes parcours sur la même base
 * @param label
 */
export const labelFor = (label: string): string =>
  test.info().project.name === 'natheo_atelier' ? `${label} A` : label;
