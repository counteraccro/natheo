/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Mise en forme des dates et des nombres dans la langue de la page
 */
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import type { FrontPluralTranslate } from '@/ts/Front/FrontTranslate.type';

export interface Format {
  /** Date seule, en toutes lettres (long) ou en chiffres (short) ; null pour une valeur vide */
  date(value: string | null, style?: 'long' | 'short'): string | null;
  /** Date et heure ; null pour une valeur vide */
  dateTime(value: string | null): string | null;
  /** Libellé accordé au nombre */
  plural(count: number, forms: FrontPluralTranslate): string;
}

const DATE_STYLES: Record<'long' | 'short', Intl.DateTimeFormatOptions> = {
  long: { day: 'numeric', month: 'long', year: 'numeric' },
  short: { day: 'numeric', month: 'numeric', year: 'numeric' },
};

export function useFormat(): Format {
  const locale = useFrontContext().datas.locale;
  const rules = new Intl.PluralRules(locale);
  const numbers = new Intl.NumberFormat(locale);

  return {
    date: (value, style = 'long') => (value ? new Date(value).toLocaleDateString(locale, DATE_STYLES[style]) : null),
    dateTime: (value) => (value ? new Date(value).toLocaleString(locale) : null),
    plural: (count, forms) =>
      (rules.select(count) === 'one' ? forms.one : forms.other).replace(/#/g, numbers.format(count)),
  };
}
