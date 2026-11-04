/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Libellés communs aux composants génériques (accessibilité, erreur générique),
 * traduits côté serveur et injectés une seule fois dans la page (#ui-translate)
 */
import type { UiTranslateKey } from '@/ts/Global/UiTranslate.type';

let labels: Partial<Record<UiTranslateKey, string>> | null = null;

/**
 * Retourne le libellé traduit, ou la clé si la page ne fournit pas les traductions
 * @param key clé du libellé
 */
export const uiTrans = (key: UiTranslateKey): string => {
  if (labels === null) {
    try {
      labels = JSON.parse(document.getElementById('ui-translate')?.textContent ?? '{}');
    } catch {
      labels = {};
    }
  }
  return labels?.[key] ?? key;
};
