/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Parcours du module FAQ dans l'application d'administration, joué dans chaque thème du back-office : formulaire de
 * la FAQ et arbre de ses catégories et questions
 */
import { expect, test, type Page } from '@playwright/test';
import { storageState } from './accounts';
import { currentTheme, labelFor } from './themes';
import { FAQ_LIST } from './themes/theme';

const toasts = (page: Page) => page.getByRole('region', { name: 'Notifications' });
const confirmDialog = (page: Page) => page.locator('dialog[open]');
const row = (page: Page, label: string) => currentTheme().row(page, label, FAQ_LIST);
const openList = (page: Page): Promise<void> => currentTheme().openList(page, FAQ_LIST);
const titleField = (page: Page) => page.getByRole('textbox', { name: 'Titre (Français) (obligatoire)' }).first();

/**
 * Crée une FAQ par le formulaire, puis attend son édition avec l'arbre
 */
const createFaq = async (page: Page, title: string): Promise<void> => {
  await page.goto('/admin/fr/faqs/new');
  await expect(page.getByRole('heading', { level: 2, name: 'Contenu' })).toHaveCount(0);
  await titleField(page).fill(title);
  await page.getByRole('button', { name: 'Créer', exact: true }).click();
  await expect(toasts(page)).toContainText(`FAQ « ${title} » créée.`);
  await expect(page).toHaveURL(/\/admin\/fr\/faqs\/\d+$/);
  await expect(page.getByRole('button', { name: 'Nouvelle question', exact: true })).toBeVisible();
};

/**
 * Ouvre le menu d'une ligne de l'arbre et choisit une action
 */
const lineAction = async (page: Page, menu: string, action: string): Promise<void> => {
  await page.getByRole('button', { name: menu }).click();
  await page.getByRole('menuitem', { name: action }).click();
};

test.describe('administrateur', () => {
  test.use({ storageState: storageState('admin') });

  test('liste avec ses compteurs', async ({ page }) => {
    await openList(page);
    await expect(row(page, 'FAQ Produit')).toBeVisible();
    await expect(row(page, 'FAQ Interne')).toBeVisible();
  });

  test('création puis arbre : question, catégorie, déplacement, désactivation', async ({ page }) => {
    await createFaq(page, labelFor('FAQ parcours'));

    await page.getByRole('button', { name: 'Nouvelle question', exact: true }).click();
    const question = page.getByRole('group', { name: 'Modifier la question' });
    await question.getByRole('textbox', { name: 'Question (Français) (obligatoire)' }).fill('Combien de temps ?');
    await expect(question).toContainText('Modifications non enregistrées');
    await question.getByRole('button', { name: 'Enregistrer' }).click();
    await expect(toasts(page)).toContainText('Question enregistrée.');
    await expect(page.getByRole('button', { name: 'Combien de temps ?', exact: true })).toBeVisible();

    await page.getByRole('button', { name: 'Ajouter une catégorie' }).click();
    const category = page.getByRole('group', { name: 'Nouvelle catégorie' });
    await category.getByRole('textbox', { name: 'Titre (Français) (obligatoire)' }).fill('Livraison');
    await category.getByRole('textbox', { name: 'Titre (Français) (obligatoire)' }).press('Enter');
    await expect(toasts(page)).toContainText('Catégorie créée.');
    await expect(page.getByRole('button', { name: 'Livraison', exact: true })).toBeVisible();

    await lineAction(page, 'Actions de la question « Combien de temps ? »', 'Livraison');
    await expect(toasts(page)).toContainText('Question déplacée dans « Livraison ».');
    await expect(page.getByText('2 catégories · 1 question')).toBeVisible();

    await lineAction(page, 'Actions de la catégorie « Livraison »', 'Désactiver');
    await expect(confirmDialog(page)).toContainText('et 1 question active sera désactivée avec elle');
    await confirmDialog(page).getByRole('button', { name: 'Désactiver' }).click();
    await expect(toasts(page)).toContainText('Catégorie désactivée avec sa question.');

    await page.getByRole('button', { name: 'Combien de temps ?', exact: true }).click();
    await expect(page.getByText('Cette question et sa catégorie « Livraison » sont désactivées')).toBeVisible();
  });

  test('modification concurrente refusée (412)', async ({ page, browser }) => {
    await createFaq(page, labelFor('FAQ concurrence'));
    const url = page.url();

    const other = await browser.newPage({ storageState: storageState('admin') });
    await other.goto(url);
    await titleField(other).fill(labelFor('FAQ autre onglet'));
    await other.getByRole('button', { name: 'Enregistrer' }).first().click();
    await expect(toasts(other)).toContainText('enregistrée');
    await other.close();

    await titleField(page).fill(labelFor('FAQ premier onglet'));
    await page.getByRole('button', { name: 'Enregistrer' }).first().click();
    await expect(page.getByText('Modifié entre-temps').first()).toBeVisible();
  });

  test('suppression refusée pour une FAQ affichée par une page, acceptée sinon', async ({ page }) => {
    await openList(page);
    await currentTheme().rowAction(page, 'FAQ Produit', 'Supprimer', FAQ_LIST);
    await confirmDialog(page).getByRole('button', { name: 'Supprimer' }).click();
    await expect(toasts(page)).toContainText('Cette FAQ est utilisée par 1 page');

    await createFaq(page, labelFor('FAQ suppression'));
    await openList(page);
    await currentTheme().rowAction(page, labelFor('FAQ suppression'), 'Supprimer', FAQ_LIST);
    await confirmDialog(page).getByRole('button', { name: 'Supprimer' }).click();
    await expect(toasts(page)).toContainText(`FAQ « ${labelFor('FAQ suppression')} » supprimée.`);
    await expect(row(page, labelFor('FAQ suppression'))).toHaveCount(0);
  });
});

test.describe('contributeur', () => {
  test.use({ storageState: storageState('contributor') });

  test("consultation de la FAQ d'un autre, arbre en lecture seule", async ({ page }) => {
    await openList(page);
    await expect(currentTheme().rowActions(page, 'FAQ Interne', FAQ_LIST)).toHaveCount(0);
    // Natheo-Atelier ouvre l'élément par son lien, Natheo-Studio par l'action « Consulter »
    const line = row(page, 'FAQ Interne');
    await line
      .getByRole('link', { name: 'FAQ Interne' })
      .or(line.getByRole('button', { name: 'Consulter' }))
      .click();

    await expect(page.getByRole('button', { name: 'Ajouter une catégorie' })).toHaveCount(0);
    await page.getByRole('button', { name: 'Qui contacter ?', exact: true }).click();
    const question = page.getByRole('group', { name: 'Question' });
    await expect(question.getByRole('textbox', { name: 'Question (Français) (obligatoire)' })).toBeDisabled();
    await expect(question.getByRole('button', { name: 'Enregistrer' })).toHaveCount(0);
  });
});
