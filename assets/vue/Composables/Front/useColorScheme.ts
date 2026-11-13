/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Mode clair / sombre du site public : choix mémorisé du visiteur, sinon préférence du système
 */
import { onBeforeUnmount, readonly, shallowRef, type Ref } from 'vue';

export type ColorScheme = 'light' | 'dark';

/** Clé lue aussi par le script de front_base.html.twig avant le rendu */
const STORAGE_KEY = 'theme';

export interface ColorSchemeState {
  scheme: Readonly<Ref<ColorScheme>>;
  toggle: () => void;
}

export function useColorScheme(): ColorSchemeState {
  const media = window.matchMedia('(prefers-color-scheme: dark)');
  const scheme = shallowRef<ColorScheme>(document.documentElement.classList.contains('dark') ? 'dark' : 'light');

  const apply = (value: ColorScheme): void => {
    scheme.value = value;
    document.documentElement.classList.toggle('dark', value === 'dark');
  };

  const stored = (): string | null => {
    try {
      return localStorage.getItem(STORAGE_KEY);
    } catch {
      return null;
    }
  };

  const onSystemChange = (event: MediaQueryListEvent): void => {
    if (stored() === null) {
      apply(event.matches ? 'dark' : 'light');
    }
  };
  media.addEventListener('change', onSystemChange);
  onBeforeUnmount(() => media.removeEventListener('change', onSystemChange));

  const toggle = (): void => {
    const value: ColorScheme = scheme.value === 'dark' ? 'light' : 'dark';
    apply(value);
    try {
      localStorage.setItem(STORAGE_KEY, value);
    } catch {
      // Stockage indisponible (navigation privée) : le choix vaut pour la page en cours
    }
  };

  return { scheme: readonly(scheme), toggle };
}
