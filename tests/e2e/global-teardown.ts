/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Base de test vidée après les tests, comme l'attend PHPUnit
 */
import { execFileSync } from 'node:child_process';

export default function globalTeardown(): void {
  execFileSync('php', ['bin/console', '--env=test', 'natheo:install-bdd-test', '--no-interaction'], {
    stdio: 'ignore',
  });
}
