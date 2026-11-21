/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Base de test recréée puis remplie par seed.php avant les tests
 */
import { execFileSync } from 'node:child_process';

export default function globalSetup(): void {
  execFileSync('php', ['bin/console', '--env=test', 'natheo:install-bdd-test', '--no-interaction'], {
    stdio: 'inherit',
  });
  execFileSync('php', ['tests/e2e/seed.php'], { stdio: ['ignore', 'ignore', 'inherit'] });
}
