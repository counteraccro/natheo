/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Formats des écrans du back-office : date et heure, date relative, nom d'une langue
 */

const UNITS: [Intl.RelativeTimeFormatUnit, number][] = [
  ['year', 31_536_000],
  ['month', 2_592_000],
  ['week', 604_800],
  ['day', 86_400],
  ['hour', 3_600],
  ['minute', 60],
];

/**
 * Date et heure courtes (21 nov. 2026, 10:40)
 * @param iso date ISO 8601
 * @param locale langue de l'interface
 */
export function formatDateTime(iso: string | null | undefined, locale: string): string {
  if (!iso) return '';
  return new Intl.DateTimeFormat(locale, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(iso));
}

/**
 * Date relative (il y a 3 jours)
 * @param iso date ISO 8601
 * @param locale langue de l'interface
 * @param now instant de référence, en millisecondes
 */
export function formatRelative(iso: string | null | undefined, locale: string, now: number = Date.now()): string {
  if (!iso) return '';
  const seconds = Math.round((new Date(iso).getTime() - now) / 1000);
  const format = new Intl.RelativeTimeFormat(locale, { numeric: 'auto' });
  const [unit, size] = UNITS.find(([, length]) => Math.abs(seconds) >= length) ?? ['second', 1];
  return format.format(Math.round(seconds / size), unit);
}

/**
 * Nom d'une langue dans la langue de l'interface, initiale en majuscule (fr → Français)
 * @param code
 * @param locale langue de l'interface
 */
export function localeName(code: string, locale: string): string {
  const name = new Intl.DisplayNames([locale], { type: 'language' }).of(code) ?? code;
  return name.charAt(0).toLocaleUpperCase(locale) + name.slice(1);
}
