/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Connexion de chaque compte par le formulaire, session gardée pour les tests
 */
import { expect, test as setup } from '@playwright/test';
import { accounts, storageState, type AccountKey } from './accounts';

for (const key of Object.keys(accounts) as AccountKey[]) {
  setup(`connexion ${key}`, async ({ page }) => {
    await page.goto('/fr/admin/login');
    await page.getByLabel('Adresse email').fill(accounts[key].email);
    await page.locator('input[name="password"]').fill(accounts[key].password);
    await page.getByRole('button', { name: 'Connexion' }).click();
    await expect(page).toHaveURL(/\/admin\/fr\/dashboard/);
    await page.context().storageState({ path: storageState(key) });
  });
}
