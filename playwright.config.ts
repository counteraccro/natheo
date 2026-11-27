/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Tests de bout en bout du back-office : serveur PHP intégré en environnement de test, sur la base de test
 */
import { defineConfig, devices } from '@playwright/test';

const port = Number(process.env.E2E_PORT ?? 8765);
const baseURL = process.env.E2E_BASE_URL ?? `http://127.0.0.1:${port}`;

export default defineConfig({
  testDir: 'tests/e2e',
  // Un seul serveur PHP et une seule base : les tests modifient des données partagées
  fullyParallel: false,
  workers: 1,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? [['list'], ['html', { open: 'never' }]] : 'list',
  globalSetup: './tests/e2e/global-setup.ts',
  globalTeardown: './tests/e2e/global-teardown.ts',
  use: {
    baseURL,
    locale: 'fr-FR',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects: [
    { name: 'setup', testMatch: /auth\.setup\.ts/ },
    {
      name: 'natheo_studio',
      use: { ...devices['Desktop Chrome'] },
      dependencies: ['setup'],
    },
    // La disposition est une option de l'utilisateur en base : Natheo-Atelier est choisi après les tests de Studio
    { name: 'setup_atelier', testMatch: /layout\.setup\.ts/, dependencies: ['natheo_studio'] },
    {
      name: 'natheo_atelier',
      use: { ...devices['Desktop Chrome'] },
      dependencies: ['setup_atelier'],
    },
  ],
  // Port occupé : échec plutôt que de jouer les tests sur un autre serveur (base de dev)
  webServer: process.env.E2E_BASE_URL
    ? undefined
    : {
        command: `php -S 127.0.0.1:${port} -t public tests/e2e/router.php`,
        url: `${baseURL}/fr/admin/login`,
        reuseExistingServer: false,
        stderr: 'ignore',
        timeout: 60_000,
      },
});
