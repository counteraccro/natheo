/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Choix de la disposition Natheo-Atelier par le menu Apparence pour chaque compte, enregistré dans ses options
 */
import { expect, test as setup } from '@playwright/test';
import { accounts, storageState, type AccountKey } from './accounts';

for (const key of Object.keys(accounts) as AccountKey[]) {
  setup(`disposition Natheo-Atelier ${key}`, async ({ browser }) => {
    const page = await browser.newPage({ storageState: storageState(key) });
    await page.goto('/admin/fr/tags');
    await page.getByRole('button', { name: 'Apparence' }).click();
    await page.getByRole('menuitemradio', { name: 'Natheo-Atelier' }).click();
    await expect(page.getByRole('navigation', { name: 'Sections' })).toBeVisible();
    await page.close();
  });
}
