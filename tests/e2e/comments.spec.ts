/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Parcours du module Commentaires dans l'application d'administration, joué dans chaque thème du back-office :
 * filtre « À modérer », modération groupée, modération d'un commentaire et conflit
 */
import { expect, test, type Page } from '@playwright/test';
import { storageState } from './accounts';
import { currentTheme } from './themes';
import { COMMENT_LIST, type ListScreen } from './themes/theme';

const TO_MODERATE: ListScreen = { ...COMMENT_LIST, path: `${COMMENT_LIST.path}?moderation=wait_validation` };

/**
 * Auteur propre au projet : aucun n'est le début d'un autre, la ligne de Natheo-Studio est cherchée par son début
 */
const author = (name: string): string =>
  `${name} ${test.info().project.name === 'natheo_atelier' ? 'Atelier' : 'Studio'}`;

const toasts = (page: Page) => page.getByRole('region', { name: 'Notifications' });
const row = (page: Page, name: string, list: ListScreen = COMMENT_LIST) => currentTheme().row(page, name, list);
const statusField = (page: Page) => page.getByRole('combobox', { name: 'Statut' });

/**
 * Ouvre le formulaire d'un commentaire depuis la liste
 */
const openComment = async (page: Page, name: string): Promise<void> => {
  await currentTheme().openList(page, COMMENT_LIST);
  // Natheo-Atelier ouvre l'élément par son lien, Natheo-Studio par l'action « Modifier »
  const line = row(page, name);
  await line
    .getByRole('link', { name, exact: true })
    .or(line.getByRole('button', { name: 'Modifier' }))
    .click();
  await expect(page).toHaveURL(/\/admin\/fr\/comments\/\d+$/);
  await expect(statusField(page)).toBeVisible();
};

test.describe('administrateur', () => {
  test.use({ storageState: storageState('admin') });

  test('filtre « À modérer » lu dans l’adresse', async ({ page }) => {
    await currentTheme().openList(page, TO_MODERATE);
    await expect(row(page, author('Bruno'), TO_MODERATE)).toBeVisible();
    await expect(row(page, author('Chloé'), TO_MODERATE)).toHaveCount(0);
  });

  test('modération groupée avec sa raison', async ({ page }) => {
    await currentTheme().openList(page, TO_MODERATE);
    await row(page, author('Alice'), TO_MODERATE).getByRole('checkbox').check();
    await row(page, author('Bruno'), TO_MODERATE).getByRole('checkbox').check();
    await currentTheme().bulkAction(page, 'Modérer');

    const dialog = page.getByRole('dialog', { name: 'Modérer 2 commentaires' });
    await dialog.getByRole('textbox', { name: 'Commentaire de modération' }).fill('Hors sujet');
    await dialog.getByRole('button', { name: 'Modérer' }).click();
    await expect(toasts(page)).toContainText('2 éléments traités');
    await expect(row(page, author('Alice'), TO_MODERATE)).toHaveCount(0);
    await expect(row(page, author('Bruno'), TO_MODERATE)).toHaveCount(0);
  });

  test('modération d’un commentaire depuis son formulaire', async ({ page }) => {
    await openComment(page, author('Chloé'));
    const reason = page.getByRole('textbox', { name: 'Commentaire de modération' });
    await expect(reason).toHaveCount(0);

    await statusField(page).selectOption({ label: 'Modéré' });
    await reason.fill('Propos déplacés');
    await page.getByRole('button', { name: 'Enregistrer' }).first().click();
    await expect(toasts(page)).toContainText(`Commentaire de ${author('Chloé')} enregistré`);
    await expect(page.getByText(/Modéré par e2e-admin/)).toBeVisible();
  });

  test('modification concurrente refusée (412)', async ({ page, browser }) => {
    await openComment(page, author('Denis'));
    const url = page.url();

    const other = await browser.newPage({ storageState: storageState('admin') });
    await other.goto(url);
    await statusField(other).selectOption({ label: 'En attente' });
    await other.getByRole('button', { name: 'Enregistrer' }).first().click();
    await expect(toasts(other)).toContainText('enregistré');
    await other.close();

    await statusField(page).selectOption({ label: 'Modéré' });
    await page.getByRole('button', { name: 'Enregistrer' }).first().click();
    await expect(page.getByText('Modifié entre-temps').first()).toBeVisible();
  });
});
