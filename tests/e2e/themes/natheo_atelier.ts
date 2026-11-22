/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Gestes de liste de Natheo-Atelier : liste de l'établi, actions dans le menu de la ligne, tri dans les options
 */
import { expect } from '@playwright/test';
import type { ThemeScreens } from './theme';

const list = (page: Parameters<ThemeScreens['openList']>[0]) => page.getByRole('list', { name: 'Gestion des tags' });

export const natheoAtelier: ThemeScreens = {
  async openList(page) {
    await page.goto('/admin/fr/tags');
    await expect(page.getByRole('heading', { level: 1, name: 'Gestion des tags' })).toBeVisible();
    await expect(list(page).getByRole('listitem').first()).toBeVisible();
  },
  row: (page, label) =>
    list(page)
      .getByRole('listitem')
      .filter({ has: page.getByRole('link', { name: label, exact: true }) }),
  rows: (page) => list(page).getByRole('listitem'),
  async rowAction(page, label, action) {
    // Liste défilante : le défilement fait avant le clic fermerait le menu qui vient de s'ouvrir
    const more = natheoAtelier.row(page, label).getByRole('button', { name: "Plus d'actions" });
    await more.scrollIntoViewIfNeeded();
    await expect(more).toBeInViewport();
    await more.click();
    await page.getByRole('menuitem', { name: action }).click();
  },
  rowActions: (page, label) => natheoAtelier.row(page, label).getByRole('button', { name: "Plus d'actions" }),
  async search(page, term) {
    await page.getByRole('searchbox', { name: 'Rechercher dans le tableau' }).fill(term);
  },
  async sortByLabel(page) {
    await natheoAtelier.openOptions(page);
    await page.getByRole('menuitem', { name: 'Trier par Libellé' }).click();
  },
  async openOptions(page) {
    await page.getByRole('button', { name: 'Options' }).click();
  },
  async bulkAction(page, action) {
    await page.getByRole('region', { name: 'Sélection' }).getByRole('button', { name: action }).click();
  },
};
