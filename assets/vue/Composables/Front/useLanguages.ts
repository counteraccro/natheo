/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Langues du site : libellé traduit, page affichée dans cette langue (accueil à défaut), langue affichée signalée
 */
import { computed, type ComputedRef } from 'vue';
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';

export interface Language {
  locale: string;
  label: string;
  url: string;
  current: boolean;
}

export function useLanguages(): ComputedRef<Language[]> {
  const context = useFrontContext();
  const { urls, datas, translate } = context;
  const labels: Record<string, string> = {
    fr: translate.footer.frLink,
    en: translate.footer.enLink,
    es: translate.footer.esLink,
  };

  const urlOf = (locale: string): string => {
    const home = urls.home[locale] ?? '/';
    const page = context.page.value;
    const translation = page?.landingPage ? undefined : page?.translations.find((t) => t.locale === locale);
    if (page === null || translation === undefined) {
      return home;
    }
    // La prévisualisation garde sa requête pour rester sur le brouillon
    const query = datas.preview !== null ? window.location.search : '';
    return context.pageUrl(page.category, translation.slug, locale) + query;
  };

  return computed(() =>
    datas.locales.map((locale) => ({
      locale,
      label: labels[locale] ?? locale,
      url: urlOf(locale),
      current: locale === datas.locale,
    }))
  );
}
