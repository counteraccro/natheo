/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Gestes de liste de Natheo-Atelier : liste de l'établi, actions dans le menu de la ligne, tri dans les options
 */
import { expect } from '@playwright/test';
import { TAG_LIST, type ListScreen, type ThemeScreens } from './theme';

const list = (page: Parameters<ThemeScreens['openList']>[0], screen: ListScreen) =>
  page.getByRole('list', { name: screen.title });

export const natheoAtelier: ThemeScreens = {
  async openList(page, screen = TAG_LIST) {
    await page.goto(screen.path);
    await expect(page.getByRole('heading', { level: 1, name: screen.title })).toBeVisible();
    await expect(list(page, screen).getByRole('listitem').first()).toBeVisible();
  },
  row: (page, label, screen = TAG_LIST) =>
    list(page, screen)
      .getByRole('listitem')
      .filter({ has: page.getByRole('link', { name: label, exact: true }) }),
  rows: (page, screen = TAG_LIST) => list(page, screen).getByRole('listitem'),
  async rowAction(page, label, action, screen = TAG_LIST) {
    // Liste défilante : le défilement fait avant le clic fermerait le menu qui vient de s'ouvrir
    const more = natheoAtelier.row(page, label, screen).getByRole('button', { name: "Plus d'actions" });
    await more.scrollIntoViewIfNeeded();
    await expect(more).toBeInViewport();
    await more.click();
    await page.getByRole('menuitem', { name: action }).click();
  },
  rowActions: (page, label, screen = TAG_LIST) =>
    natheoAtelier.row(page, label, screen).getByRole('button', { name: "Plus d'actions" }),
  async openRow(page, label, screen = TAG_LIST) {
    await natheoAtelier.row(page, label, screen).getByRole('link', { name: label, exact: true }).click();
  },
  async backToList() {},
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
