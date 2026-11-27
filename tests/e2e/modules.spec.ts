/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Parcours de l'écran « Modules » : désactivation d'une opération puis d'un module, joué dans chaque thème
 */
import { expect, test, type Page } from '@playwright/test';
import { storageState } from './accounts';
import { labelFor } from './themes';

const toasts = (page: Page) => page.getByRole('region', { name: 'Notifications' });
const confirmDialog = (page: Page) => page.locator('dialog[open]');
const moduleSwitch = (page: Page, label: string) => page.getByRole('switch', { name: label, exact: true });
const tagOperations = (page: Page) => page.locator('#module-operations-tag');

const openModules = async (page: Page): Promise<void> => {
  await page.goto('/admin/fr/modules');
  await expect(page.getByRole('heading', { level: 1, name: 'Modules' })).toBeVisible();
  await expect(moduleSwitch(page, 'Tags')).toBeVisible();
};

const openTagOperations = async (page: Page): Promise<void> => {
  await page
    .getByRole('button', { name: /opérations d'API/ })
    .and(page.locator('[aria-controls="module-operations-tag"]'))
    .click();
  await expect(tagOperations(page)).toBeVisible();
};

/** Bascule un interrupteur et valide la confirmation demandée à la désactivation */
const disable = async (page: Page, toggle: ReturnType<Page['getByRole']>): Promise<void> => {
  await toggle.click();
  await confirmDialog(page).getByRole('button', { name: 'Désactiver' }).click();
};

/** Tout réactiver, pour laisser la base dans son état de départ */
const enableAll = async (page: Page): Promise<void> => {
  await openModules(page);
  if ((await moduleSwitch(page, 'Tags').getAttribute('aria-checked')) === 'false') {
    await moduleSwitch(page, 'Tags').click();
    await expect(moduleSwitch(page, 'Tags')).toHaveAttribute('aria-checked', 'true');
  }
  await openTagOperations(page);
  const deleteSwitch = tagOperations(page).getByRole('switch', { name: 'Supprimer' });
  if ((await deleteSwitch.getAttribute('aria-checked')) === 'false') {
    await deleteSwitch.click();
    await expect(deleteSwitch).toHaveAttribute('aria-checked', 'true');
  }
};

test.describe('super administrateur', () => {
  test.use({ storageState: storageState('superAdmin') });

  test.afterEach(async ({ page }) => {
    await enableAll(page);
  });

  test('modules verrouillés non désactivables', async ({ page }) => {
    await openModules(page);
    await expect(moduleSwitch(page, 'Modules')).toBeDisabled();
    await expect(moduleSwitch(page, 'Tableau de bord')).toBeDisabled();
    await expect(moduleSwitch(page, 'Tags')).toBeEnabled();
  });

  test('opération désactivée : l’action disparaît, le reste du module reste disponible', async ({ page }) => {
    await page.goto('/admin/fr/tags/new');
    await page.getByRole('textbox', { name: 'Libellé (Français) (obligatoire)' }).fill(labelFor('Parcours modules'));
    await page.getByRole('button', { name: 'Créer' }).click();
    await expect(page).toHaveURL(/\/admin\/fr\/tags\/\d+$/);
    const tagUrl = page.url();
    await expect(page.getByRole('button', { name: 'Supprimer' })).toBeVisible();

    await openModules(page);
    await openTagOperations(page);
    await disable(page, tagOperations(page).getByRole('switch', { name: 'Supprimer' }));
    await expect(toasts(page)).toContainText('Opération « DELETE /admin/tags/{id} » désactivée');

    await page.goto(tagUrl);
    await expect(page.getByRole('textbox', { name: 'Libellé (Français) (obligatoire)' })).toHaveValue(
      labelFor('Parcours modules')
    );
    await expect(page.getByRole('button', { name: 'Supprimer' })).toHaveCount(0);
  });

  test('module désactivé : absent du menu, page introuvable à son adresse', async ({ page }) => {
    await openModules(page);
    await disable(page, moduleSwitch(page, 'Tags'));
    await expect(toasts(page)).toContainText('Module « Tags » désactivé');
    await expect(page.getByRole('link', { name: 'Gestion des tags' })).toHaveCount(0);

    await page.goto('/admin/fr/tags');
    await expect(page.getByText('Page introuvable').first()).toBeVisible();
  });
});
