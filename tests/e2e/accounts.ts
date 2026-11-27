/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Comptes créés par seed.php et fichier de session de chacun
 */
import accounts from './accounts.json' with { type: 'json' };

export type AccountKey = keyof typeof accounts;

export { accounts };

export const storageState = (key: AccountKey): string => `tests/e2e/.auth/${key}.json`;
