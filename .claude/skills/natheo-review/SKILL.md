---
name: natheo-review
description: Relecture d'une branche ou d'une PR de Nathéo selon les conventions du projet (CLAUDE.md, CONTRIBUTING.md) - conventions, sécurité, bugs prouvés, doublons à refactoriser, workflow GitHub - avec tests ciblés. Utiliser quand l'utilisateur demande une review, une relecture ou un contrôle avant PR/merge sur Nathéo.
argument-hint: "[numéro de PR | branche] [--suivi]"
context: fork
---

# Review Nathéo

Tu relis le travail de quelqu'un d'autre, sans connaître ses intentions : juge le code tel qu'il est, pas tel qu'il devait être.

Relecture en lecture seule : ne modifier aucun fichier du projet, ne rien commiter, ne rien publier sur GitHub. Seule écriture autorisée : le rapport dans `.ai/review/`. Le rapport est rendu en français, en dernier message, tel quel : il sera affiché à l'utilisateur.

## 1. Référentiel

Lire `.claude/CLAUDE.md` et `CONTRIBUTING.md` de la branche relue **au début de chaque review**. Ce sont les seules sources des conventions : ce skill ne les recopie pas et ne les remplace pas. En cas de doute sur une règle, citer la section de CLAUDE.md concernée.

## 2. Déterminer la cible

Argument reçu : `$ARGUMENTS`

- **Numéro de PR** : `gh pr view <n> --json number,title,body,baseRefName,headRefName,commits,files`, puis `gh pr diff <n>`. Si la branche de la PR n'est pas la branche courante, `git fetch origin <head>` puis lire les fichiers via `git show origin/<head>:<fichier>`.
- **Nom de branche** ou **aucun argument** (branche courante) :
  1. si une PR existe pour la branche (`gh pr view <branche> --json ...`), prendre sa base et sa description ;
  2. sinon, base = la branche d'Epic dont elle part (`git branch -a --list '*epic/E-*'`, celle dont le `git merge-base` est le plus récent), à défaut `master` ;
  3. diff : `git diff <base>...<branche>`, plus les changements non commités (`git status`, `git diff HEAD`) si c'est la branche courante.
- **`--suivi`** : seconde passe après corrections, voir § 7.

Annoncer en une ligne la cible retenue (branche, base, PR éventuelle, nombre de fichiers et de commits).

## 3. Stratégie de lecture

- **Jusqu'à ~25 fichiers modifiés** (hors lock, traductions et fichiers générés) : lire chaque fichier modifié en entier, et les fichiers qu'il appelle si besoin.
- **Au-delà** (ex. PR d'Epic vers `master`) : ne pas tout charger d'un coup.
  1. Établir la carte : `git diff --stat <base>...<branche>` et `git log --oneline <base>..<branche>`.
  2. Relire par lots cohérents, dans cet ordre : migrations et entités, services et repositories, contrôleurs et API, Twig/Vue/SCSS, traductions, tests. Pour chaque lot, lire les hunks avec leur contexte (`git diff -U30`) et n'ouvrir en entier que les fichiers nouveaux ou dont un hunk ne suffit pas à juger.
  3. Noter les constats au fil des lots (fichier, ligne, constat) avant de passer au suivant, pour ne pas dépendre de ce qui a été lu plus tôt.
  4. Une PR d'Epic regroupe des PR déjà relues : se concentrer sur les interactions entre elles (doublons, incohérences, migrations dans le bon ordre, conflits de merge mal résolus).
- Le dire dans le rapport si une partie n'a été lue qu'en diff.

## 4. Axes de relecture

Ne signaler que ce qui concerne le code modifié par la branche, pas la dette déjà présente ailleurs, sauf si le changement l'aggrave ou la recopie.

### Conventions
Appliquer toutes les sections de `.claude/CLAUDE.md` qui concernent les fichiers touchés (placement du code, couches, en-tête des fichiers PHP, commentaires, admin, TypeScript, traductions, migrations, sécurité admin, API v2…). Pour chaque écart : la règle, la section, la ligne fautive.

Contrôles spécifiques à ce skill :
- **Mapping Doctrine sans migration** : si une entité ou un attribut `#[ORM\…]` change, vérifier la présence de la migration sur les deux plateformes, et lancer `php bin/console doctrine:schema:validate --env=test` (lecture seule) pour détecter un écart entre mapping et schéma.
- **Fixtures et helpers de test** : un nouveau champ, une nouvelle entité ou un nouveau cas d'enum doit être repris dans `src/DataFixtures` (et ses YAML de `src/DataFixtures/data`, démo comprise) et dans `tests/Helper/Fixtures` quand les tests en ont besoin.
- **Dépendances** : si `composer.json` ou `package.json` change, vérifier que le lock suit (`composer.lock`, `yarn.lock`, `symfony.lock`), que la version est compatible avec la stack (Symfony, API Platform, Vue, Vite), que le paquet est maintenu, et lancer `composer audit` / `yarn npm audit` si disponible. Un lock modifié sans changement de manifeste est suspect.
- **Documentation** : une branche qui crée une convention, une commande, une variable d'environnement ou change un comportement documenté doit mettre à jour `.claude/CLAUDE.md`, `CONTRIBUTING.md` ou `README.md`.
- **Mode sombre** : pour les écrans admin modifiés, vérifier qu'aucune couleur n'est codée en dur (hex, `text-gray-*`, `bg-white`, `text-black`…) là où une variable de thème existe, et que chaque fond a un texte de thème lisible dans les deux modes.

### Sécurité
Appliquer « Admin security conventions » et la sécurité de l'API de CLAUDE.md, plus : SQL brut paramétré, `|raw` Twig et `v-html` sur une donnée non maîtrisée, données sensibles dans les logs ou les réponses, droits vérifiés sur l'entité. Une faille trouvée ne se décrit jamais publiquement : la signaler dans le rapport et rappeler `SECURITY.md`.

### Bugs et logique
**Tout bug doit être prouvé par le code.** Un bug n'est signalé que si le chemin qui le déclenche est démontré : citer les lignes en cause (`fichier:ligne`), l'entrée ou l'état concret qui le provoque, et le résultat faux obtenu (exception, mauvaise valeur, donnée exposée). Si possible, le confirmer par un test existant en échec ou une commande en lecture seule. Ce qui n'est pas démontré n'est pas un bug : le mettre dans « Suggestions » comme point à vérifier en disant ce qui manque pour conclure, ou ne pas le mentionner.

À examiner : conditions inversées, cas limites (null, liste vide, locale absente, contenu non traduit), types PHP/TS incohérents, régressions sur les appelants des méthodes modifiées (chercher les usages), N+1 et fetch-join manquants, transactions/verrous/concurrence, exceptions avalées, comportement ajouté non couvert par un test, test qui ne vérifie rien de réel.

### Doublons à refactoriser
Chercher activement (Grep/Glob sur `src/`, `assets/`, `templates/`, `tests/Helper/`) si le code ajouté existe déjà ou se répète :
- logique déjà présente dans un service, trait, enum, composable, composant Vue ou composant Twig existant, qu'il aurait fallu réutiliser ;
- blocs copiés-collés entre fichiers de la branche (même méthode dans deux services, même template, même requête Doctrine, mêmes helpers de test) ;
- constantes ou listes de valeurs recopiées au lieu d'être lues depuis l'enum ou la configuration source.

Pour chaque doublon : les emplacements des deux côtés (`fichier:ligne`), la cible de factorisation conforme au placement du projet, et l'ampleur (correction locale à la branche, ou refacto plus large à sortir dans une issue `[CLEAN CODE]`).

### Workflow GitHub
Appliquer `CONTRIBUTING.md` et « GitHub workflow » de CLAUDE.md : nom de branche, base (branche d'Epic pour une sous-issue, vérifiée avec `gh issue view <n>`), titre de PR et `Closes #<n>` cohérents avec la branche, template rempli et coché. Commits découpés en unités cohérentes et annulables seules (back + tests, front, traductions, fixtures/config, migration), pas un commit unique, messages en français, aucun fichier parasite. Ne pas signaler `src/DataFixtures/data/system/user_fixtures_data.yaml`, régénéré par chaque run PHPUnit.

## 5. Vérifications ciblées

Lancer uniquement ce que la branche touche, sans modifier la base ni les fichiers :
- **PHP** : le test miroir de chaque fichier de `src/` modifié s'il existe (`src/Service/Admin/X/FooService.php` → `tests/Service/Admin/X/FooServiceTest.php`, idem `Controller/`, `Repository/`, `Api/V2/` → `tests/Api/V2/`…) et les tests modifiés par la branche : `php bin/phpunit --display-deprecations <fichiers>`.
- **Dépréciations** : relever celles qu'affiche PHPUnit et indiquer celles dont la trace pointe vers un fichier modifié par la branche.
- **Traductions** modifiées : `php bin/phpunit tests/Translation`.
- **Migrations** ajoutées : `php bin/phpunit tests/Doctrine` et `doctrine:schema:validate` (§ 4) ; ne pas exécuter les migrations.
- **Front** (`.ts`/`.vue`) modifié : `yarn type-check`.

Si une commande échoue pour une raison d'environnement (base de test absente…), le dire et continuer. Rapporter les échecs réels avec l'extrait utile de la sortie.

## 6. Rapport

```
## Review <branche> → <base> (PR #n si existante)

<1 à 3 phrases : ce que fait la branche, avis global. Parties lues en diff seulement, le cas échéant.>

### Bloquant
- [B1] `fichier:ligne` — problème. Preuve : entrée/état → lignes parcourues → résultat faux. Correction proposée.

### À corriger
- [C1] `fichier:ligne` — écart. Règle (section CLAUDE.md). Correction proposée.

### Doublons à refactoriser
- [D1] `a.php:12` ≈ `b.php:40` — ce qui est dupliqué. Factorisation proposée (cible, ampleur).

### Suggestions
- [S1] …

### Workflow GitHub
- ✅ / ❌ branche, base, titre, Closes, commits, template

### Vérifications
- ✅ / ❌ / ⚠️ commande — résultat
- Restant à faire à la main : migrations up/down sur MySQL et PostgreSQL, `yarn build`, rendu en mode sombre…
```

Règles :
- **Bloquant** : bug ou faille prouvés par le code, test en échec, convention vérifiée par la CI ou les tests (workflow PR, tests de traduction, migration manquante sur une plateforme, schéma invalide).
- **À corriger** : écart aux conventions, régression démontrée sur un appelant, test, fixture ou documentation manquants, dépendance non verrouillée.
- **Suggestions** : lisibilité, nommage, simplification, points non démontrés à vérifier.
- Identifiants `[B1]`, `[C1]`, `[D1]`, `[S1]` stables, pour la seconde passe.
- Chaque point est vérifié dans le code (lu, pas supposé) et ancré sur `fichier:ligne` ; omettre une section vide ; pas de compliment de remplissage.

Enregistrer le rapport dans `.ai/review/<branche avec / remplacés par ->.md` (écraser l'ancien), puis le rendre en dernier message, suivi de la question : quels points corriger ?

## 7. Seconde passe (`--suivi`)

1. Lire le rapport précédent `.ai/review/<branche>.md` ; s'il n'existe pas, le dire et faire une review complète.
2. Pour chaque point du rapport, vérifier dans le code actuel : ✅ corrigé (citer la correction), ❌ toujours présent, ➖ abandonné (point non retenu par l'utilisateur, si le commit ou la conversation l'indique).
3. Relire uniquement le code modifié depuis le rapport (`git diff` entre le commit noté dans le rapport et `HEAD`) pour repérer ce que les corrections auraient cassé ou introduit ; nouveaux points numérotés à la suite.
4. Relancer les vérifications ciblées des fichiers modifiés depuis.
5. Rapport court : tableau des points (id, statut, détail), puis nouveaux points éventuels, puis vérifications. Mettre à jour `.ai/review/<branche>.md`.

Pour que la seconde passe fonctionne, la première ligne du rapport enregistré contient le commit relu : `<!-- commit: <sha de HEAD> -->`.
