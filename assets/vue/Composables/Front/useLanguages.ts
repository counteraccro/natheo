/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Langues du site : libellé traduit, accueil dans cette langue, langue affichée signalée
 */
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';

export interface Language {
  locale: string;
  label: string;
  url: string;
  current: boolean;
}

export function useLanguages(): Language[] {
  const { urls, datas, translate } = useFrontContext();
  const labels: Record<string, string> = {
    fr: translate.footer.frLink,
    en: translate.footer.enLink,
    es: translate.footer.esLink,
  };
  return datas.locales.map((locale) => ({
    locale,
    label: labels[locale] ?? locale,
    url: urls.home[locale] ?? '/',
    current: locale === datas.locale,
  }));
}
