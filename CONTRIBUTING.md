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
| 🔍 Audit | restitution de l'audit d'un module | `[AUDIT] …` |
| ✨ Feature / évolution | nouvelle fonctionnalité ou amélioration | `[FEATURE] …` |
| 🧹 Clean code | refactoring, performance, dépréciation, documentation | `[CLEAN CODE] …` |
| 🗂️ Epic | regrouper plusieurs issues (ex. un audit de module) | `[EPIC] …` |

Ce qui est fait automatiquement à la création (workflow `issue-triage`) :
- ajout du numéro dans le titre, pour les bugs uniquement ;
- labels de type, module, priorité et couche d'après les réponses du formulaire ;
- milestone, s'il n'y en a qu'un seul ouvert (la version en cours).

Les points de sécurité d'un audit sont détaillés dans une advisory privée ([SECURITY.md](SECURITY.md)) ; l'issue d'audit n'en cite que la correction attendue.

Une Epic a sa propre branche (voir [Branche d'Epic](#branche-depic)). Ses issues filles lui sont rattachées en **sous-issues** (panneau « Sub-issues » de l'Epic, qui affiche l'avancement) ; la rubrique « Issues » les cite dans l'ordre de traitement, et liste en texte celles qui ne sont pas encore créées.

### 2. La branche

Elle part de `master` à jour (de la branche de son Epic pour une issue fille), avec le numéro de l'issue :

| Issue | Branche |
|---|---|
| Bug | `bug/B-<n>-<slug>` |
| Audit | `audit/A-<n>-<slug>` |
| Feature, évolution, clean code | `feature/N-<n>-<slug>` |
| Epic | `epic/E-<n>-<slug>` |
| Maintenance sans issue (montée de version, configuration) | `maintenance/<slug>` |
| Version publiée à corriger (voir [Versions maintenues](#versions-maintenues)) | `release/<majeure>.<mineure>` |

Le `<slug>` est en minuscules, avec des tirets : `bug/B-363-audit-dashboard`.

Un correctif destiné à une version déjà sortie part de la branche `release/<version>` (voir [Versions maintenues](#versions-maintenues)).

### 3. La pull request

- Titre : `Bug/B-<n> Titre`, `Audit/A-<n> Titre`, `Feature/N-<n> Titre` ou `Epic/E-<n> Titre`.
- Base : `master`, ou la branche de l'Epic pour une issue fille.
- Description : le template commence par `Closes #<n>`. C'est ce qui lie la PR à l'issue et ferme l'issue au merge.
- Les vérifications du template (tests, build, traductions, migration, fixtures) sont à cocher avant de demander la relecture.

Ce qui est fait automatiquement (workflow `pull-request`) :
- labels de technologie et de couche selon les fichiers modifiés ;
- recopie depuis l'issue de ses labels (hors statut) et du milestone ;
- contrôle du nom de branche, du titre et de `Closes #<n>` : le check échoue si l'un des trois ne respecte pas la convention ;
- au merge dans une branche d'Epic, fermeture de l'issue liée (GitHub ne le fait qu'au merge sur `master`).

### Branche d'Epic

Les issues d'une Epic ne passent pas une à une sur `master` :

1. à la première issue de l'Epic, créer `epic/E-<n>-<slug>` depuis `master` à jour (`<n>` : numéro de l'Epic) ;
2. chaque issue fille part de cette branche, et sa PR la vise ; l'issue est fermée au merge ;
3. pour reprendre les nouveautés de `master`, merger `master` dans la branche d'Epic ;
4. quand toutes les issues sont faites, ouvrir la PR `Epic/E-<n> Titre` de la branche d'Epic vers `master`, avec `Closes #<n>` : l'Epic est fermée au merge.

## Versions maintenues

`master` porte la prochaine version mineure (`app.version` en `x.y.0-dev`). Chaque version publiée est marquée par un tag `vx.y.z` et une release GitHub.

Une branche `release/x.y` n'est créée que lorsqu'une version publiée a besoin d'un correctif :

1. créer `release/x.y` depuis le dernier tag de cette version (ex. `v2.0.0`) ;
2. faire la branche du correctif (`bug/B-<n>-<slug>`) depuis `release/x.y`, et ouvrir la PR vers `release/x.y` ;
3. passer `app.version` au numéro du correctif (ex. `2.0.1`), taguer `vx.y.z` et publier la release ;
4. reporter le correctif sur `master` (merge de `release/x.y` ou cherry-pick), pour qu'il ne disparaisse pas de la version suivante.

Une migration ajoutée dans un correctif est reportée sur `master` avec le même numéro de version, sur les deux plateformes.

Le milestone d'un correctif est celui de sa version (`Sortie v2.0.1`), créé à ce moment-là.

## Labels

Le référentiel est [`.github/labels.yml`](.github/labels.yml). Pour ajouter ou modifier un label, on modifie ce fichier : il est appliqué au dépôt au merge sur `master`.

Les labels n'ont pas de préfixe : leur famille se reconnaît à la couleur et à la description. Les noms de type, module, priorité et couche reprennent exactement les options des formulaires.

| Famille | Labels | Posé par |
|---|---|---|
| Type | `Bug`, `Audit`, `Feature`, `Évolution`, `Clean code`, `Régression`, `Dépréciation`, `Documentation`, `Sécurité`, `Dépendances`, `Epic`, `Question` (un seul par issue) | formulaire |
| Priorité | `Critique`, `Medium`, `Bas` | formulaire |
| Module | zone fonctionnelle (`Pages`, `FAQ`, `Site public`…) | formulaire |
| Couche | `Back`, `Front`, `API` (`API` sert aussi de module) | formulaire (issue), fichiers modifiés (PR) |
| Technologie | `PHP`, `TypeScript`, `JavaScript`, `Twig`, `CI` | fichiers modifiés (PR) |
| Statut | `En attente de merge`, `Doublon`, `Invalide`, `Ne sera pas traité` | à la main |

La version cible passe par le **milestone** (`Sortie v2.0.0`…). Les anciens labels de version (`v2.0.0`…) sont conservés pour l'historique mais ne sont plus posés.
