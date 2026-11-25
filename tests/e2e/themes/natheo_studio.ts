/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Gestes de liste de Natheo-Studio : grille, actions par ligne et barre de sélection
 */
import { expect } from '@playwright/test';
import { TAG_LIST, type ListScreen, type ThemeScreens } from './theme';

const table = (page: Parameters<ThemeScreens['openList']>[0], list: ListScreen) =>
  page.getByRole('table', { name: list.title });

export const natheoStudio: ThemeScreens = {
  async openList(page, list = TAG_LIST) {
    await page.goto(list.path);
    await expect(page.getByRole('heading', { level: 1, name: list.title })).toBeVisible();
    await expect(table(page, list).getByRole('row').nth(1)).toBeVisible();
  },
  row: (page, label) => page.getByRole('row', { name: new RegExp(`Sélectionner ${label} `) }),
  rows: (page, list = TAG_LIST) => table(page, list).getByRole('rowgroup').nth(1).getByRole('row'),
  async rowAction(page, label, action) {
    const row = natheoStudio.row(page, label);
    if (action === 'Supprimer') {
      await row.getByRole('button', { name: "Plus d'actions" }).click();
      await page.getByRole('menuitem', { name: action }).click();
    } else {
      await row.getByRole('button', { name: action }).click();
    }
  },
  rowActions: (page, label) =>
    natheoStudio.row(page, label).getByRole('button', { name: /^(Modifier|Activer|Désactiver)$/ }),
  async search(page, term) {
    const box = page.getByRole('searchbox', { name: 'Rechercher dans le tableau' });
    await box.fill(term);
    await box.press('Enter');
  },
  async sortByLabel(page) {
    await page.getByRole('button', { name: 'Libellé' }).click();
  },
  async openOptions(page) {
    await page.getByRole('button', { name: 'Options' }).click();
  },
  async bulkAction(page, action) {
    await page.getByRole('button', { name: action }).and(page.locator(':not(tr *)')).click();
  },
};
