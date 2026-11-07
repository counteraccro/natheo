<!--
Titre attendu : "Bug/B-<n> Titre", "Audit/A-<n> Titre" ou "Feature/N-<n> Titre" (numéro de l'issue).
Branche attendue : bug/B-<n>-<slug>, audit/A-<n>-<slug> ou feature/N-<n>-<slug>, ou maintenance/<slug> pour une maintenance sans issue.
Un correctif d'une version déjà publiée cible la branche release/<majeure>.<mineure> au lieu de master.
Les labels tech/couche et le milestone sont posés automatiquement.
-->

Closes #

## Résumé
_Ce que corrige ou apporte cette PR._

## Changements
- 

## Points d'attention pour la relecture
_Choix discutables, effets de bord, zones à tester en priorité. Sinon : « Aucun »._

## Vérifications
- [ ] `php bin/phpunit --display-deprecations`
- [ ] `yarn build` (vue-tsc + vite)
- [ ] Traductions fr / en / es à jour
- [ ] Migration Doctrine : aucune / ajoutée
- [ ] Fixtures et helpers de test à jour
