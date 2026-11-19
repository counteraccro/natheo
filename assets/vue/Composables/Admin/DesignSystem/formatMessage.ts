/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Formatage des messages ICU du catalogue des composants : paramètres simples et pluriels (=0, one, other)
 */

/**
 * Remplace les paramètres d'un message
 * @param message message ICU
 * @param params valeurs des paramètres
 * @param locale langue des règles de pluriel
 */
export function formatMessage(message: string, params: Record<string, string | number> = {}, locale = 'fr'): string {
  const rules = new Intl.PluralRules(locale);
  const plural = /\{(\w+),\s*plural,\s*((?:[=\w]+\s*\{[^{}]*\}\s*)+)\}/g;
  const withPlurals = message.replace(plural, (_, name: string, cases: string) => {
    const value = Number(params[name]);
    const options = Object.fromEntries([...cases.matchAll(/([=\w]+)\s*\{([^{}]*)\}/g)].map((m) => [m[1], m[2]]));
    const chosen = options[`=${value}`] ?? options[rules.select(value)] ?? options.other ?? '';
    return chosen.replace(/#/g, String(value));
  });
  return withPlurals.replace(/\{(\w+)\}/g, (match, name: string) => (name in params ? String(params[name]) : match));
}
