# Contribuer à Natheo CMS

Vous souhaitez contribuer au projet ? Contactez-moi :

- en message privé sur Discord : `counteraccro`
- directement depuis le Discord de Natheo : https://discord.gg/CgcMCcbmaR

Une faille de sécurité se signale en privé : voir [SECURITY.md](SECURITY.md).

## Workflow

Chaque sujet suit le même chemin : **une issue → une branche → une PR liée à l'issue**. Aucun commit direct sur `master`.

### 1. L'issue

Elle se crée depuis l'un des formulaires (les issues libres sont désactivées) :

| Formulaire | Pour | Titre |
|---|---|---|
| 🐞 Bug | anomalie constatée (hors faille) | `[BUG #n] …` |
| 🔍 Audit | restitution de l'audit d'un module | `[AUDIT #n] …` |
| ✨ Feature / évolution | nouvelle fonctionnalité ou amélioration | `[FEATURE #n] …` |
| 🧹 Clean code | refactoring, performance, dépréciation, documentation | `[CLEAN CODE #n] …` |
| 🗂️ Epic | regrouper plusieurs issues (ex. un audit de module) | `[EPIC #n] …` |

Ce qui est fait automatiquement à la création (workflow `issue-triage`) :
- ajout du numéro dans le titre ;
- labels `type:`, `module:`, `priorité:` et `couche:` d'après les réponses du formulaire ;
- milestone, s'il n'y en a qu'un seul ouvert (la version en cours).

Les points de sécurité d'un audit sont détaillés dans une advisory privée ([SECURITY.md](SECURITY.md)) ; l'issue d'audit n'en cite que la correction attendue.

Une Epic liste ses issues filles en cases à cocher et n'a pas de branche.

### 2. La branche

Elle part de `master` à jour, avec le numéro de l'issue :

| Issue | Branche |
|---|---|
| Bug | `bug/B-<n>-<slug>` |
| Audit | `audit/A-<n>-<slug>` |
| Feature, évolution, clean code | `feature/N-<n>-<slug>` |
| Maintenance sans issue (montée de version, configuration) | `maintenance/<slug>` |

Le `<slug>` est en minuscules, avec des tirets : `bug/B-363-audit-dashboard`.

### 3. La pull request

- Titre : `Bug/B-<n> Titre`, `Audit/A-<n> Titre` ou `Feature/N-<n> Titre`.
- Description : le template commence par `Closes #<n>`. C'est ce qui lie la PR à l'issue et ferme l'issue au merge.
- Les vérifications du template (tests, build, traductions, migration, fixtures) sont à cocher avant de demander la relecture.

Ce qui est fait automatiquement (workflow `pull-request`) :
- labels `tech:` et `couche:` selon les fichiers modifiés ;
- recopie depuis l'issue des labels `type:`, `module:`, `priorité:` et du milestone ;
- contrôle du nom de branche, du titre et de `Closes #<n>` : le check échoue si l'un des trois ne respecte pas la convention.

## Labels

Le référentiel est [`.github/labels.yml`](.github/labels.yml). Pour ajouter ou modifier un label, on modifie ce fichier : il est appliqué au dépôt au merge sur `master`.

| Famille | Rôle | Posé par |
|---|---|---|
| `type:` | nature du sujet, un seul par issue | formulaire |
| `priorité:` | `critique`, `medium`, `bas` | formulaire |
| `module:` | zone fonctionnelle | formulaire |
| `couche:` | `back`, `front`, `api` | formulaire (issue), fichiers modifiés (PR) |
| `tech:` | `php`, `typescript`, `javascript`, `twig`, `ci` | fichiers modifiés (PR) |
| `statut:` | `en attente de merge`, `doublon`, `invalide`, `ne sera pas traité` | à la main |

La version cible passe par le **milestone** (`Sortie v2.0.0`…). Les anciens labels de version (`v2.0.0`…) sont conservés pour l'historique mais ne sont plus posés.
