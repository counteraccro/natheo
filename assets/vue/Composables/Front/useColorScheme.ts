/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Mode clair / sombre du site public : choix mémorisé du visiteur, sinon préférence du système ; dans l'aperçu de
 * l'éditeur de menu, mode imposé par l'éditeur et jamais mémorisé
 */
import { onBeforeUnmount, readonly, shallowRef, type Ref } from 'vue';

export type ColorScheme = 'light' | 'dark';

/** Clé lue aussi par le script de front_base.html.twig avant le rendu */
const STORAGE_KEY = 'frontColorScheme';

export interface ColorSchemeState {
  scheme: Readonly<Ref<ColorScheme>>;
  toggle: () => void;
}

/** Un seul mode par page affichée, partagé par tous les composants */
const scheme = shallowRef<ColorScheme>(document.documentElement.classList.contains('dark') ? 'dark' : 'light');
let previewMode = false;
let onPreviewToggle: ((value: ColorScheme) => void) | null = null;

function apply(value: ColorScheme): void {
  scheme.value = value;
  document.documentElement.classList.toggle('dark', value === 'dark');
}

function stored(): string | null {
  try {
    return localStorage.getItem(STORAGE_KEY);
  } catch {
    return null;
  }
}

/**
 * Mode imposé par l'éditeur de menu à son aperçu : le choix du visiteur n'est plus lu ni écrit
 */
export function setPreviewColorScheme(value: ColorScheme): void {
  previewMode = true;
  apply(value);
}

/**
 * Appelle `listener` quand le bouton du thème change le mode de l'aperçu, pour en informer l'éditeur
 */
export function onPreviewColorSchemeToggle(listener: ((value: ColorScheme) => void) | null): void {
  onPreviewToggle = listener;
}

export function useColorScheme(): ColorSchemeState {
  const media = window.matchMedia('(prefers-color-scheme: dark)');

  const onSystemChange = (event: MediaQueryListEvent): void => {
    if (!previewMode && stored() === null) {
      apply(event.matches ? 'dark' : 'light');
    }
  };
  media.addEventListener('change', onSystemChange);
  onBeforeUnmount(() => media.removeEventListener('change', onSystemChange));

  const toggle = (): void => {
    const value: ColorScheme = scheme.value === 'dark' ? 'light' : 'dark';
    apply(value);
    if (previewMode) {
      onPreviewToggle?.(value);
      return;
    }
    try {
      localStorage.setItem(STORAGE_KEY, value);
    } catch {
      // Stockage indisponible (navigation privée) : le choix vaut pour la page en cours
    }
  };

  return { scheme: readonly(scheme), toggle };
}
