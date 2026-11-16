/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Mise en forme des dates et des nombres dans la langue de la page
 */
import { useFrontContext } from '@/vue/Composables/Front/useFrontContext';
import type { FrontPluralTranslate } from '@/ts/Front/FrontTranslate.type';

export interface Format {
  /** Date seule, en toutes lettres (long) ou en chiffres (short) ; null pour une valeur vide */
  date(value: string | null, style?: 'long' | 'short'): string | null;
  /** Date et heure ; null pour une valeur vide */
  dateTime(value: string | null): string | null;
  /** Date relative à maintenant (« il y a 3 jours ») ; null pour une valeur vide */
  relative(value: string | null): string | null;
  /** Libellé accordé au nombre */
  plural(count: number, forms: FrontPluralTranslate): string;
}

const DATE_STYLES: Record<'long' | 'short', Intl.DateTimeFormatOptions> = {
  long: { day: 'numeric', month: 'long', year: 'numeric' },
  short: { day: 'numeric', month: 'numeric', year: 'numeric' },
};

const RELATIVE_UNITS: [Intl.RelativeTimeFormatUnit, number][] = [
  ['year', 31536000],
  ['month', 2592000],
  ['week', 604800],
  ['day', 86400],
  ['hour', 3600],
  ['minute', 60],
];

export function useFormat(): Format {
  const locale = useFrontContext().datas.locale;
  const rules = new Intl.PluralRules(locale);
  const numbers = new Intl.NumberFormat(locale);
  const relatives = new Intl.RelativeTimeFormat(locale, { numeric: 'auto' });

  return {
    date: (value, style = 'long') => (value ? new Date(value).toLocaleDateString(locale, DATE_STYLES[style]) : null),
    dateTime: (value) => (value ? new Date(value).toLocaleString(locale) : null),
    relative: (value) => {
      if (!value) {
        return null;
      }
      const seconds = (new Date(value).getTime() - Date.now()) / 1000;
      const [unit, size] = RELATIVE_UNITS.find(([, size]) => Math.abs(seconds) >= size) ?? ['minute', 60];
      return relatives.format(Math.round(seconds / size), unit);
    },
    plural: (count, forms) =>
      (rules.select(count) === 'one' ? forms.one : forms.other).replace(/#/g, numbers.format(count)),
  };
}
