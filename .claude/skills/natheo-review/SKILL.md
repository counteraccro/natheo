---
name: natheo-review
description: Relecture d'une branche ou d'une PR de Nathéo selon les conventions du projet (CLAUDE.md, CONTRIBUTING.md) - conventions, sécurité, bugs, doublons à refactoriser, workflow GitHub - avec tests ciblés. Utiliser quand l'utilisateur demande une review, une relecture ou un contrôle avant PR/merge sur Nathéo.
argument-hint: "[numéro de PR | branche]"
---

# Review Nathéo

Relecture en lecture seule : ne modifier aucun fichier, ne rien commiter, ne rien publier sur GitHub. Le rapport est rendu dans le terminal, en français. Les corrections ne sont faites que si l'utilisateur le demande après le rapport.

## 1. Déterminer la cible

Argument reçu : `$ARGUMENTS`

- **Numéro de PR** : `gh pr view <n> --json number,title,body,baseRefName,headRefName,commits,files`, puis `gh pr diff <n>`. Si la branche de la PR est la branche courante, lire les fichiers en local ; sinon lire le contenu via `git show origin/<head>:<fichier>` après `git fetch origin <head>`.
- **Nom de branche** ou **aucun argument** (branche courante) :
  1. si une PR existe pour la branche (`gh pr view <branche> --json ...`), prendre sa base et sa description ;
  2. sinon, base = la branche d'Epic dont elle part (`git branch -a --list '*epic/E-*'`, choisir celle dont le `git merge-base` est le plus récent), à défaut `master` ;
  3. diff : `git diff <base>...<branche>` + changements non commités (`git status`, `git diff HEAD`) si c'est la branche courante.

Annoncer en une ligne la cible retenue (branche, base, PR éventuelle, nombre de fichiers et de commits).

Lire chaque fichier modifié **en entier** (pas seulement les hunks) et, si besoin, les fichiers qu'il appelle : un diff ne suffit pas pour juger les doublons ou les régressions.

## 2. Axes de relecture

Le référentiel est `.claude/CLAUDE.md` (déjà chargé) et `CONTRIBUTING.md`. Ne signaler que ce qui concerne le code modifié par la branche, pas la dette déjà présente ailleurs (sauf si le changement l'aggrave ou la recopie).

### Conventions du projet
- **Placement** : pas de `src/Utils/` ni `assets/utils/` ; constantes en enums ; `Factory`/`Populate`/`Formatter` dans leurs dossiers ; traits dans `Entity/Trait` ou `Repository/Trait` ; arborescence miroir `Controller/Service/Repository/Entity/Enum` et `tests/`.
- **Couches** : contrôleur fin, logique dans un service `App*Service` ; nouveaux collaborateurs via `HANDLERS` / `AutowireLocator`.
- **En-tête PHP** : tout nouveau fichier PHP a, sous `declare(strict_types=1);`, un docblock avec description, `@author Gourdon Aymeric`, `@version`.
- **Commentaires** : courts (2 lignes max pour `//`), expliquent le pourquoi, ne parlent que de l'élément commenté, jamais de `#n`, Epic, PR, « lot ».
- **Admin (Twig/Vue/SCSS)** : `<twig:Admin:PageHeaderComponent>` et titre de page selon les règles ; breadcrumb d'une page fille contenant la route de sa grille ; boutons (`btn` + taille + variante, ordre Retour/action, icônes, pas de `btn-secondary`, pas d'utilitaires Tailwind de padding sur `.btn`) ; utilitaires de thème au lieu de `var(--…)` en style inline ou valeur arbitraire.
- **TypeScript** : types dans `assets/ts/<Domaine>/<Nom>.type.ts`, import `@/ts/...` ; logique réutilisable dans `assets/vue/Composables/`.
- **Traductions** : clés `<domaine>.<écran>.<élément>`, minuscules, pas de clé préfixe d'une autre, une clé par domaine pour les libellés communs, `domain:` explicite sur chaque `trans()`, valeurs en fr/en/es sans marqueur `__`, paramètres ICU identiques ; nouveau domaine ajouté à `DOMAINS` de `TranslationKeyConventionTest`.
- **Migrations** : chaque migration en double (Mysql + Postgresql), même numéro, bon dossier/namespace de version, `skipIf()` de plateforme, SQL portable ou écrit par plateforme, identifiants réservés quotés.
- **API v2** : toutes les règles de la section « API v2 » de CLAUDE.md (layout `src/Api/V2`, DTO non-entités, `required: true`, mappers sans service, un provider/processor par opération, `NotFoundException`, groupes `<resource>:read|list`, filtres construits depuis la source, `errors:` déclarés, `security:` par opération, tests avec assertions de schéma). La v1 n'est pas un modèle : signaler un nom, format ou structure recopié de la v1.

### Sécurité
- Valeurs saisies placées dans `GridService::KEY_DATA` échappées avec `htmlspecialchars($v, ENT_QUOTES)` ; couleurs validées par `/^#[0-9a-fA-F]{6}$/` ; `v-html` sur une donnée non maîtrisée.
- Actions AJAX d'écriture protégées par `checkCsrf()` côté contrôleur et `withCsrf()` côté Vue (clé `'csrf'` dans les actions de grille).
- Droits de contenu (Page, FAQ, Menu, Tag) via `checkContentAccess()` / `ContentVoter` ; seul l'admin supprime le contenu des autres.
- API : rôle déclaré sur chaque opération, droits vérifiés sur l'entité dans le processor/service, ressource non publiée jamais exposée ni liée.
- SQL brut paramétré, aucune concaténation d'entrée utilisateur ; `|raw` Twig justifié ; données sensibles dans les logs ou les réponses.
- Une faille trouvée ne se décrit pas dans un commentaire public : la signaler à l'utilisateur et rappeler `SECURITY.md`.

### Bugs et logique
**Tout bug doit être prouvé par le code.** Un bug n'est signalé que si le chemin qui le déclenche est démontré : citer les lignes en cause (`fichier:ligne`), l'entrée ou l'état concret qui le provoque, et le résultat faux obtenu (exception, mauvaise valeur, donnée exposée). Si possible, le confirmer par un test existant en échec ou une commande en lecture seule. Ce qui n'est pas démontré n'est pas un bug : le mettre dans « Suggestions » comme point à vérifier, en disant ce qui manque pour conclure, ou ne pas le mentionner.

- Erreurs de logique, conditions inversées, cas limites (null, liste vide, locale absente, contenu non traduit), types PHP/TS incohérents.
- Régressions sur les appelants des méthodes modifiées (chercher les usages avec Grep).
- Requêtes : N+1 (boucle déclenchant des chargements lazy), fetch-join manquant sur une collection, requête dans un mapper.
- Transactions, verrous et concurrence sur les écritures ; exceptions avalées.
- Tests : le comportement ajouté ou corrigé est-il couvert ? Un test qui ne vérifie rien de réel ?

### Doublons à refactoriser
Chercher activement (Grep/Glob sur `src/`, `assets/`, `templates/`, `tests/Helper/`) si le code ajouté existe déjà ou se répète :
- logique déjà présente dans un service, trait, enum, composable, composant Vue ou composant Twig existant, qu'il aurait fallu réutiliser ;
- blocs copiés-collés entre fichiers de la branche (même méthode dans deux services, même template Vue/Twig, même requête Doctrine, mêmes helpers de test) ;
- constantes ou listes de valeurs recopiées au lieu d'être lues depuis l'enum ou la configuration source.

Pour chaque doublon : citer les emplacements (`fichier:ligne` des deux côtés), proposer la cible de factorisation conforme au placement du projet (service, trait, composable, composant…), et estimer l'ampleur (locale à la branche, ou refacto plus large à sortir dans une issue `[CLEAN CODE]`).

### Workflow GitHub
- Branche : `bug/B-<n>-<slug>`, `audit/A-<n>-<slug>`, `feature/N-<n>-<slug>`, `epic/E-<n>-<slug>`, `maintenance/<slug>` ou `release/<x.y>` ; jamais de travail sur `master`.
- Base : branche d'Epic pour une issue fille (vérifier que l'issue est une sous-issue d'une Epic avec `gh issue view <n>`), `master` sinon ; une branche d'Epic ne vise que `master`.
- PR (si elle existe) : titre `Bug/B-<n> Titre` (préfixe et numéro cohérents avec la branche), description commençant par `Closes #<n>` (même numéro), sections du template remplies, vérifications cochées.
- Commits : découpés en unités cohérentes et annulables seules (back + tests, front, traductions, fixtures/config, migration) ; pas un commit unique pour toute la PR ; messages en français ; pas de fichier parasite (dump, `.env.local`, fichiers générés). Ne pas signaler `user_fixtures_data.yaml`, régénéré par chaque run PHPUnit.

## 3. Vérifications ciblées

Lancer uniquement ce que la branche touche, sans modifier la base de données ni les fichiers :
- **PHP** : pour chaque fichier de `src/` modifié, le test miroir s'il existe (`src/Service/Admin/X/FooService.php` → `tests/Service/Admin/X/FooServiceTest.php`, idem `Controller/`, `Repository/`, `Api/V2/` → `tests/Api/V2/`…), plus les tests modifiés par la branche : `php bin/phpunit --display-deprecations <fichiers>`.
- **Traductions** modifiées (`translations/`) : `php bin/phpunit tests/Translation`.
- **Migrations** ajoutées : `php bin/phpunit tests/Doctrine` (ne pas exécuter les migrations elles-mêmes, le signaler comme vérification manuelle restante).
- **Front** (`assets/` `.ts`/`.vue`) modifié : `yarn type-check`.

Si une commande échoue pour une raison d'environnement (base de test absente…), le dire et continuer. Rapporter les échecs réels avec l'extrait utile de la sortie.

## 4. Rapport

Format, en français :

```
## Review <branche> → <base> (PR #n si existante)

<1 à 3 phrases : ce que fait la branche, avis global>

### Bloquant
- `fichier:ligne` — problème. Preuve : entrée/état → lignes parcourues → résultat faux. Correction proposée.

### À corriger
- …

### Doublons à refactoriser
- `a.php:12` ≈ `b.php:40` — ce qui est dupliqué. Factorisation proposée (cible, ampleur).

### Suggestions
- …

### Workflow GitHub
- ✅ / ❌ branche, base, titre, Closes, commits, template

### Vérifications
- ✅ / ❌ / ⚠️ commande — résultat
- Restant à faire à la main : (ex. migrations up/down sur MySQL et PostgreSQL, `doctrine:schema:validate`, `yarn build`)
```

Règles :
- **Bloquant** : bug ou faille prouvés par le code (voir « Bugs et logique »), test en échec, convention vérifiée par la CI ou les tests (workflow PR, tests de traduction, migration manquante sur une plateforme).
- **À corriger** : écart aux conventions de CLAUDE.md, régression démontrée sur un appelant, test manquant.
- **Suggestions** : lisibilité, nommage, simplification.
- Chaque point est vérifié dans le code (lu, pas supposé) et ancré sur `fichier:ligne` ; omettre une section vide ; pas de compliment de remplissage.
- Terminer en demandant quels points corriger, sans rien modifier avant la réponse.
