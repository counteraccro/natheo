/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Parcours du module Tags dans l'application d'administration, joué dans chaque thème du back-office
 */
import { expect, test, type Page } from '@playwright/test';
import { storageState } from './accounts';
import { currentTheme, labelFor } from './themes';

const toasts = (page: Page) => page.getByRole('region', { name: 'Notifications' });
const confirmDialog = (page: Page) => page.locator('dialog[open]');
const row = (page: Page, label: string) => currentTheme().row(page, label);
const openList = (page: Page): Promise<void> => currentTheme().openList(page);

/**
 * Crée un tag par le formulaire et revient à la liste
 */
const createTag = async (page: Page, label: string): Promise<void> => {
  await page.goto('/admin/fr/tags/new');
  await page.getByRole('textbox', { name: 'Libellé (Français) (obligatoire)' }).fill(label);
  await page.getByRole('button', { name: 'Créer' }).click();
  await expect(toasts(page)).toContainText(`Tag « ${label} » créé.`);
  await expect(page).toHaveURL(/\/admin\/fr\/tags\/\d+$/);
};

test.describe('administrateur', () => {
  test.use({ storageState: storageState('admin') });

  test('liste, recherche et tri', async ({ page }) => {
    await openList(page);
    await expect(row(page, 'Actualité')).toBeVisible();
    await expect(row(page, 'Archive')).toContainText('Désactivé');

    const theme = currentTheme();
    await theme.search(page, 'Tuto');
    await expect(theme.rows(page)).toHaveCount(1);
    await expect(row(page, 'Tutoriel')).toBeVisible();

    await theme.search(page, '');
    await theme.sortByLabel(page);
    await expect(theme.rows(page).first()).toContainText('Actualité');
  });

  test('la recherche de la liste est gardée en ouvrant un tag, au retour et au rechargement', async ({ page }) => {
    const theme = currentTheme();
    const searchbox = page.getByRole('searchbox', { name: 'Rechercher dans le tableau' });
    await openList(page);
    await theme.search(page, 'Tuto');
    await expect(theme.rows(page)).toHaveCount(1);

    await theme.openRow(page, 'Tutoriel');
    await expect(page).toHaveURL(/\/admin\/fr\/tags\/\d+\?search=Tuto$/);
    await theme.backToList(page);
    await expect(searchbox).toHaveValue('Tuto');
    await expect(theme.rows(page)).toHaveCount(1);

    await page.reload();
    await expect(searchbox).toHaveValue('Tuto');
    await expect(row(page, 'Tutoriel')).toBeVisible();
    await expect(theme.rows(page)).toHaveCount(1);
  });

  test('création avec recopie des langues, aperçu puis édition', async ({ page }) => {
    await page.goto('/admin/fr/tags/new');
    await page.getByRole('textbox', { name: 'Libellé (Français) (obligatoire)' }).fill(labelFor('Parcours création'));

    await page.getByRole('tab', { name: 'Anglais' }).first().click();
    await expect(page.getByRole('textbox', { name: 'Libellé (Anglais) (obligatoire)' })).toHaveValue(
      labelFor('Parcours création')
    );

    await page.getByRole('group', { name: 'Couleurs proposées' }).getByRole('button').first().click();
    await expect(page.frameLocator('iframe').first().getByText(labelFor('Parcours création'))).toBeVisible();

    // Champ recopié en lecture seule : décocher la recopie pour saisir l'anglais
    await expect(page.getByRole('textbox', { name: 'Libellé (Anglais) (obligatoire)' })).toBeDisabled();
    await page.getByRole('tab', { name: 'Français' }).first().click();
    await page
      .getByRole('checkbox', { name: "Recopier le texte de l'onglet Français dans les autres langues" })
      .uncheck();
    await page.getByRole('tab', { name: 'Anglais' }).first().click();
    await page.getByRole('textbox', { name: 'Libellé (Anglais) (obligatoire)' }).fill(labelFor('Creation path'));
    await page.getByRole('button', { name: 'Créer' }).click();
    await expect(toasts(page)).toContainText(`Tag « ${labelFor('Parcours création')} » créé.`);
    await expect(page).toHaveURL(/\/admin\/fr\/tags\/\d+$/);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Édition du tag #');

    await page.getByRole('textbox', { name: 'Libellé (Français) (obligatoire)' }).fill(labelFor('Parcours édition'));
    await page.getByRole('button', { name: 'Enregistrer' }).click();
    await expect(toasts(page)).toContainText(`Tag « ${labelFor('Parcours édition')} » enregistré.`);
    await page.getByRole('tab', { name: 'Anglais' }).first().click();
    await expect(page.getByRole('textbox', { name: 'Libellé (Anglais) (obligatoire)' })).toHaveValue(
      labelFor('Creation path')
    );

    await openList(page);
    await expect(row(page, labelFor('Parcours édition'))).toBeVisible();
  });

  test('modification concurrente refusée (412)', async ({ page, browser }) => {
    await createTag(page, labelFor('Parcours concurrence'));
    const url = page.url();

    const other = await browser.newPage({ storageState: storageState('admin') });
    await other.goto(url);
    await other
      .getByRole('textbox', { name: 'Libellé (Français) (obligatoire)' })
      .fill(labelFor('Concurrence autre onglet'));
    await other.getByRole('button', { name: 'Enregistrer' }).click();
    await expect(toasts(other)).toContainText('enregistré');
    await other.close();

    await page
      .getByRole('textbox', { name: 'Libellé (Français) (obligatoire)' })
      .fill(labelFor('Concurrence premier onglet'));
    await page.getByRole('button', { name: 'Enregistrer' }).click();
    await expect(page.getByText('Modifié entre-temps').first()).toBeVisible();
    await expect(page.getByRole('button', { name: 'Recharger' })).toBeVisible();
  });

  test('désactivation et activation depuis la liste', async ({ page }) => {
    await openList(page);
    await currentTheme().rowAction(page, 'Tutoriel', 'Désactiver');
    await expect(confirmDialog(page)).toContainText('Désactiver le tag « Tutoriel » ?');
    await confirmDialog(page).getByRole('button', { name: 'Désactiver' }).click();
    await expect(toasts(page)).toContainText('Tag « Tutoriel » désactivé.');
    await expect(row(page, 'Tutoriel')).toContainText('Désactivé');

    await currentTheme().rowAction(page, 'Tutoriel', 'Activer');
    await expect(toasts(page)).toContainText('Tag « Tutoriel » activé.');
    await expect(row(page, 'Tutoriel')).not.toContainText('Désactivé');
  });

  test('suppression refusée pour un tag utilisé, acceptée sinon', async ({ page }) => {
    await openList(page);
    await currentTheme().rowAction(page, 'Actualité', 'Supprimer');
    await confirmDialog(page).getByRole('button', { name: 'Supprimer' }).click();
    await expect(toasts(page)).toContainText('Ce tag est utilisé par 1 page');
    await expect(row(page, 'Actualité')).toBeVisible();

    await createTag(page, labelFor('Parcours suppression'));
    await openList(page);
    await currentTheme().rowAction(page, labelFor('Parcours suppression'), 'Supprimer');
    await expect(confirmDialog(page)).toContainText('Cette action est définitive.');
    await confirmDialog(page).getByRole('button', { name: 'Supprimer' }).click();
    await expect(toasts(page)).toContainText(`Tag « ${labelFor('Parcours suppression')} » supprimé.`);
    await expect(row(page, labelFor('Parcours suppression'))).toHaveCount(0);
  });

  test('pas de requête SQL pour un administrateur', async ({ page }) => {
    await openList(page);
    await currentTheme().openOptions(page);
    await expect(page.getByRole('menu')).toBeVisible();
    await expect(page.getByRole('menuitemcheckbox', { name: 'Afficher le SQL' })).toHaveCount(0);
  });

  test('désactivation groupée', async ({ page }) => {
    await createTag(page, labelFor('Groupe un'));
    await createTag(page, labelFor('Groupe deux'));
    await openList(page);

    await row(page, labelFor('Groupe un')).getByRole('checkbox').check();
    await row(page, labelFor('Groupe deux')).getByRole('checkbox').check();
    await currentTheme().bulkAction(page, 'Désactiver');
    await expect(confirmDialog(page)).toContainText('Désactiver 2 éléments ?');
    await confirmDialog(page).getByRole('button', { name: 'Désactiver' }).click();
    await expect(toasts(page)).toContainText('2 éléments traités');
    await expect(row(page, labelFor('Groupe un'))).toContainText('Désactivé');
    await expect(row(page, labelFor('Groupe deux'))).toContainText('Désactivé');
  });
});

test.describe('super administrateur', () => {
  test.use({ storageState: storageState('superAdmin') });

  test('requête SQL de la liste affichée et enregistrée', async ({ page }) => {
    await openList(page);
    await currentTheme().openOptions(page);
    await page.getByRole('menuitemcheckbox', { name: 'Afficher le SQL' }).click();
    const sql = page.getByRole('region', { name: 'Requête SQL' });
    await expect(sql.getByRole('code')).toContainText('FROM tag');
    await sql.getByRole('button', { name: 'Enregistrer' }).click();
    await expect(toasts(page)).toContainText('Requête enregistrée dans le gestionnaire SQL.');
  });
});

test.describe('contributeur', () => {
  test.use({ storageState: storageState('contributor') });

  test("pas d'action sur le tag d'un autre", async ({ page }) => {
    await openList(page);
    await expect(currentTheme().rowActions(page, 'Tutoriel').first()).toBeVisible();
    await expect(currentTheme().rowActions(page, 'Archive')).toHaveCount(0);
  });
});
