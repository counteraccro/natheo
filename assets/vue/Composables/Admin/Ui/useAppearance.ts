/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Couleur et mode du back-office, appliqués sur <html> (data-theme, classe dark) et mémorisés sous les mêmes clés que
 * les pages Twig pour suivre l'utilisateur d'un écran à l'autre
 */
import { readonly, ref } from 'vue';
import type { ColorMode, ThemeColor } from '@/ts/Admin/Layout/AdminHeader.type';

const THEME_KEY = 'theme';
const MODE_KEY = 'darkMode';
const THEMES: ThemeColor[] = ['violet', 'blue', 'green', 'red'];

const root = document.documentElement;
const theme = ref<ThemeColor>(THEMES.find((value) => value === root.getAttribute('data-theme')) ?? 'violet');
const mode = ref<ColorMode>(root.classList.contains('dark') ? 'dark' : 'light');

const store = (key: string, value: string): void => {
  try {
    localStorage.setItem(key, value);
  } catch {
    // Stockage indisponible : le choix vaut pour la page en cours
  }
};

/**
 * Change la couleur du thème
 */
export function setTheme(value: ThemeColor): void {
  theme.value = value;
  if (value === 'violet') root.removeAttribute('data-theme');
  else root.setAttribute('data-theme', value);
  store(THEME_KEY, value);
}

/**
 * Passe en mode clair ou sombre
 */
export function setMode(value: ColorMode): void {
  mode.value = value;
  root.classList.toggle('dark', value === 'dark');
  store(MODE_KEY, value);
}

export function useAppearance() {
  return { theme: readonly(theme), mode: readonly(mode), setTheme, setMode, themes: THEMES };
}
