/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Parcours du module Tags dans l'application d'administration
 */
import { expect, test, type Page } from '@playwright/test';
import { storageState } from './accounts';

const toasts = (page: Page) => page.getByRole('region', { name: 'Notifications' });
const confirmDialog = (page: Page) => page.locator('dialog[open]');
const row = (page: Page, label: string) => page.getByRole('row', { name: new RegExp(`Sélectionner ${label} `) });

/**
 * Ouvre la liste des tags et attend ses lignes
 */
const openList = async (page: Page): Promise<void> => {
  await page.goto('/admin/fr/tags');
  await expect(page.getByRole('heading', { level: 1, name: 'Gestion des tags' })).toBeVisible();
  await expect(page.getByRole('table', { name: 'Gestion des tags' }).getByRole('row').nth(1)).toBeVisible();
};

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

    await page.getByRole('searchbox', { name: 'Rechercher dans le tableau' }).fill('Tuto');
    await page.getByRole('searchbox', { name: 'Rechercher dans le tableau' }).press('Enter');
    await expect(page.getByRole('table').getByRole('rowgroup').nth(1).getByRole('row')).toHaveCount(1);
    await expect(row(page, 'Tutoriel')).toBeVisible();

    await page.getByRole('searchbox', { name: 'Rechercher dans le tableau' }).fill('');
    await page.getByRole('searchbox', { name: 'Rechercher dans le tableau' }).press('Enter');
    await page.getByRole('button', { name: 'Libellé' }).click();
    await expect(page.getByRole('table').getByRole('rowgroup').nth(1).getByRole('row').first()).toContainText(
      'Actualité'
    );
  });

  test('création avec recopie des langues, aperçu puis édition', async ({ page }) => {
    await page.goto('/admin/fr/tags/new');
    await page.getByRole('textbox', { name: 'Libellé (Français) (obligatoire)' }).fill('Parcours création');

    await page.getByRole('tab', { name: 'Anglais' }).first().click();
    await expect(page.getByRole('textbox', { name: 'Libellé (Anglais) (obligatoire)' })).toHaveValue(
      'Parcours création'
    );

    await page.getByRole('group', { name: 'Couleurs proposées' }).getByRole('button').first().click();
    await expect(page.frameLocator('iframe').first().getByText('Parcours création')).toBeVisible();

    // Champ recopié en lecture seule : décocher la recopie pour saisir l'anglais
    await expect(page.getByRole('textbox', { name: 'Libellé (Anglais) (obligatoire)' })).toBeDisabled();
    await page.getByRole('tab', { name: 'Français' }).first().click();
    await page
      .getByRole('checkbox', { name: "Recopier le texte de l'onglet Français dans les autres langues" })
      .uncheck();
    await page.getByRole('tab', { name: 'Anglais' }).first().click();
    await page.getByRole('textbox', { name: 'Libellé (Anglais) (obligatoire)' }).fill('Creation path');
    await page.getByRole('button', { name: 'Créer' }).click();
    await expect(toasts(page)).toContainText('Tag « Parcours création » créé.');
    await expect(page).toHaveURL(/\/admin\/fr\/tags\/\d+$/);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Édition du tag #');

    await page.getByRole('textbox', { name: 'Libellé (Français) (obligatoire)' }).fill('Parcours édition');
    await page.getByRole('button', { name: 'Enregistrer' }).click();
    await expect(toasts(page)).toContainText('Tag « Parcours édition » enregistré.');
    await page.getByRole('tab', { name: 'Anglais' }).first().click();
    await expect(page.getByRole('textbox', { name: 'Libellé (Anglais) (obligatoire)' })).toHaveValue('Creation path');

    await openList(page);
    await expect(row(page, 'Parcours édition')).toBeVisible();
  });

  test('modification concurrente refusée (412)', async ({ page, browser }) => {
    await createTag(page, 'Parcours concurrence');
    const url = page.url();

    const other = await browser.newPage({ storageState: storageState('admin') });
    await other.goto(url);
    await other.getByRole('textbox', { name: 'Libellé (Français) (obligatoire)' }).fill('Concurrence autre onglet');
    await other.getByRole('button', { name: 'Enregistrer' }).click();
    await expect(toasts(other)).toContainText('enregistré');
    await other.close();

    await page.getByRole('textbox', { name: 'Libellé (Français) (obligatoire)' }).fill('Concurrence premier onglet');
    await page.getByRole('button', { name: 'Enregistrer' }).click();
    await expect(page.getByText('Modifié entre-temps').first()).toBeVisible();
    await expect(page.getByRole('button', { name: 'Recharger' })).toBeVisible();
  });

  test('désactivation et activation depuis la liste', async ({ page }) => {
    await openList(page);
    await row(page, 'Tutoriel').getByRole('button', { name: 'Désactiver' }).click();
    await expect(confirmDialog(page)).toContainText('Désactiver le tag « Tutoriel » ?');
    await confirmDialog(page).getByRole('button', { name: 'Désactiver' }).click();
    await expect(toasts(page)).toContainText('Tag « Tutoriel » désactivé.');
    await expect(row(page, 'Tutoriel')).toContainText('Désactivé');

    await row(page, 'Tutoriel').getByRole('button', { name: 'Activer' }).click();
    await expect(toasts(page)).toContainText('Tag « Tutoriel » activé.');
    await expect(row(page, 'Tutoriel')).not.toContainText('Désactivé');
  });

  test('suppression refusée pour un tag utilisé, acceptée sinon', async ({ page }) => {
    await openList(page);
    await row(page, 'Actualité').getByRole('button', { name: "Plus d'actions" }).click();
    await page.getByRole('menuitem', { name: 'Supprimer' }).click();
    await confirmDialog(page).getByRole('button', { name: 'Supprimer' }).click();
    await expect(toasts(page)).toContainText('Ce tag est utilisé par 1 page');
    await expect(row(page, 'Actualité')).toBeVisible();

    await createTag(page, 'Parcours suppression');
    await openList(page);
    await row(page, 'Parcours suppression').getByRole('button', { name: "Plus d'actions" }).click();
    await page.getByRole('menuitem', { name: 'Supprimer' }).click();
    await expect(confirmDialog(page)).toContainText('Cette action est définitive.');
    await confirmDialog(page).getByRole('button', { name: 'Supprimer' }).click();
    await expect(toasts(page)).toContainText('Tag « Parcours suppression » supprimé.');
    await expect(row(page, 'Parcours suppression')).toHaveCount(0);
  });

  test('pas de requête SQL pour un administrateur', async ({ page }) => {
    await openList(page);
    await page.getByRole('button', { name: 'Options' }).click();
    await expect(page.getByRole('menuitemcheckbox', { name: 'ID' })).toBeVisible();
    await expect(page.getByRole('menuitemcheckbox', { name: 'Afficher le SQL' })).toHaveCount(0);
  });

  test('désactivation groupée', async ({ page }) => {
    await createTag(page, 'Groupe un');
    await createTag(page, 'Groupe deux');
    await openList(page);

    await row(page, 'Groupe un').getByRole('checkbox').check();
    await row(page, 'Groupe deux').getByRole('checkbox').check();
    await page.getByRole('button', { name: 'Désactiver' }).and(page.locator(':not(tr *)')).click();
    await expect(confirmDialog(page)).toContainText('Désactiver 2 éléments ?');
    await confirmDialog(page).getByRole('button', { name: 'Désactiver' }).click();
    await expect(toasts(page)).toContainText('2 éléments traités');
    await expect(row(page, 'Groupe un')).toContainText('Désactivé');
    await expect(row(page, 'Groupe deux')).toContainText('Désactivé');
  });
});

test.describe('super administrateur', () => {
  test.use({ storageState: storageState('superAdmin') });

  test('requête SQL de la liste affichée et enregistrée', async ({ page }) => {
    await openList(page);
    await page.getByRole('button', { name: 'Options' }).click();
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
    await expect(row(page, 'Tutoriel').getByRole('button', { name: 'Modifier' })).toBeVisible();
    await expect(row(page, 'Archive').getByRole('button', { name: 'Modifier' })).toHaveCount(0);
    await expect(row(page, 'Archive').getByRole('button', { name: 'Activer' })).toHaveCount(0);
  });
});
