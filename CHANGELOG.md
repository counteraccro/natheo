# Journal des modifications

Les changements notables de Nathéo CMS sont consignés dans ce fichier.

Le format suit [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/), avec une rubrique **Rupture** en plus pour les changements incompatibles, et le projet suit le [versionnage sémantique](https://semver.org/lang/fr/).

## [Non publié]

### Rupture

- Authentification des utilisateurs par l'API en sessions : les anciens tokens utilisateur sont invalidés par la migration, le token renvoyé par `POST /api/v1/authentication/user` est valable 15 minutes ([#505](https://github.com/counteraccro/natheo/issues/505))
- Option système de durée des sessions API (`OS_API_TIME_VALIDATE_USER_TOKEN`) limitée à 1, 7 ou 30 jours, ramenée à 1 jour par la migration ([#505](https://github.com/counteraccro/natheo/issues/505))
- Les médias de démonstration ne sont plus versionnés dans `public/assets/natheotheque`, dossier désormais ignoré par git : une mise à jour par `git pull` supprime les 14 fichiers de l'ancienne démo (`workspace.jpg`, `medias/paysage/…`, `documentations/…`). Si votre médiathèque les utilise, copiez-les hors du dépôt avant la mise à jour puis remettez-les en place ; les fichiers que vous avez ajoutés vous-même ne sont pas touchés ([#521](https://github.com/counteraccro/natheo/issues/521))
- Le thème Natheo-Horizon est réécrit : ses anciens composants (`Front/NatheoHorizon/Structure`, `Main`, `Nav`, `Footer`), `FrontApi.ts`, `UtilsFront.ts` et les constantes de `Composables/Front/Const/` sont supprimés, le conteneur `div.container` de `front_base.html.twig` est retiré et l'entrée Vite `front_natheo_horizon` pointe sur `assets/front_natheo_horizon.ts` au lieu de `assets/app_front.ts`. Une surcharge de ces fichiers est à refaire à partir du thème Natheo-Base ([#464](https://github.com/counteraccro/natheo/issues/464))
- Tokens d'API : chaque token a un type, public ou back-office. Les tokens existants deviennent publics et gardent l'accès aux opérations publiques de l'API v2 et à l'API v1. Un token back-office n'a pas de rôle propre. Il sert à connecter un utilisateur (`POST /api/v2/user_sessions`), et les opérations d'administration s'exécutent ensuite avec les droits de cet utilisateur ([#548](https://github.com/counteraccro/natheo/issues/548))
- La sidebar n'est plus stockée en base : l'écran « Sidebar » et la table `sidebar_element` sont supprimés. Une entrée ajoutée en base pour une page personnalisée est à déclarer dans un module (`#[AsAdminModule]`), les entrées désactivées réapparaissent et les libellés de la sidebar modifiés dans l'écran des traductions reprennent leur texte d'origine ([#550](https://github.com/counteraccro/natheo/issues/550))
- Icônes et logo des thèmes : `Components/Front/Global/FrontIcon.vue` est remplacé par `Components/Global/Ui/UiIcon.vue` (mêmes noms d'icônes, prop `name`), `BrandLogo.vue` passe de `Components/Front/Global/` à `Components/Global/Ui/` et le composant Twig `<twig:Front:BrandLogo>` devient `<twig:BrandLogo>`. Un thème personnalisé qui les importe change ses imports ([#517](https://github.com/counteraccro/natheo/issues/517))

### Ajouté

- Brouillon d'une page publiée : modifications enregistrées sans toucher la version en ligne, prévisualisées puis publiées ([#460](https://github.com/counteraccro/natheo/issues/460))
- Option système « Validation des pages » : les contributeurs soumettent leurs pages, un administrateur les valide ou les refuse ([#461](https://github.com/counteraccro/natheo/issues/461))
- Bloc « Pages à valider » sur le tableau de bord ([#489](https://github.com/counteraccro/natheo/issues/489))
- API v2 avec API Platform sous `/api/v2` : JSON-LD / Hydra ou JSON, erreurs `application/problem+json`, documentation Swagger sur `/api/v2/docs` quand l'API est ouverte ([#469](https://github.com/counteraccro/natheo/issues/469))
- Ressources de l'API v2 : pages, contenus et menus ([#470](https://github.com/counteraccro/natheo/issues/470)), commentaires ([#471](https://github.com/counteraccro/natheo/issues/471)), options système et sitemap ([#472](https://github.com/counteraccro/natheo/issues/472))
- Sessions API des utilisateurs : connexion, access token de 15 minutes dans `Authorization: Bearer`, renouvellement par refresh token, liste des sessions, déconnexion et `/me` ([#505](https://github.com/counteraccro/natheo/issues/505))
- Site public : socle commun aux thèmes sur l'API v2 et thème Natheo-Base (HTML sémantique sans design), sélectionnable dans les options système, avec prévisualisation des brouillons et lien « Modifier cette page » ([#463](https://github.com/counteraccro/natheo/issues/463))
- API v2 : la ressource `Page` donne ses traductions (`translations` : langue, slug et titre) ([#524](https://github.com/counteraccro/natheo/issues/524))
- Natheo-Horizon : sous-menus de l'en-tête ouverts au survol de la souris, sous-niveaux du menu déroulant en cascade, menus de l'en-tête et menu mobile sur autant de niveaux que nécessaire ; la démo montre un menu sur trois niveaux ([#531](https://github.com/counteraccro/natheo/issues/531))
- Site public : les scripts des options système (balise HEAD, début et fin de la balise BODY) sont injectés dans la page, et un bandeau de consentement activable les bloque tant que le visiteur ne les a pas acceptés. Le choix est gardé six mois et se modifie depuis le pied de page ; un bouton des options le redemande à tous les visiteurs, après un changement de politique par exemple. Nouvel onglet « Options multilingues » pour les textes du site, saisis langue par langue avec un sélecteur, où le texte du pied de page est déplacé ([#527](https://github.com/counteraccro/natheo/issues/527))
- Nouveau thème StormFront pour le site public, choisi dans les options système : un lecteur à trois volets (barre latérale à la place de l'en-tête, fil des pages de la catégorie, page ouverte sans recharger le site), gris tempête, polices Geist et Geist Mono, mode sombre et aperçu des menus dans l'éditeur. Le socle commun des thèmes sait lire les pages d'une catégorie et ouvrir une page sans rechargement (adresse, titre, métas et retour arrière du navigateur) ([#465](https://github.com/counteraccro/natheo/issues/465))
- API v2 : opérations d'administration sous `/api/v2/admin/`, réservées à une session utilisateur ouverte par un token back-office, avec les droits de l'utilisateur, filtres de recherche, de tri et de statut, droits par élément, contrôle de concurrence (`ETag` / `If-Match`, réponse 412) et journal des actions. Ces opérations peuvent encore changer avant la version 2.2 ([#548](https://github.com/counteraccro/natheo/issues/548))
- Documentation de l'API v2 avec les opérations d'administration, ouverte aux administrateurs depuis la sidebar. La documentation publique `/api/v2/docs` ne les affiche pas ([#548](https://github.com/counteraccro/natheo/issues/548), [#550](https://github.com/counteraccro/natheo/issues/550))
- Back-office : chaque module qui a plusieurs pages devient un sous-menu avec sa liste, sa création et ses autres pages (« Gestion des pages », « Nouvelle page »…). Sous « Tokens API », les entrées « Front » et « Back-office » listent les tokens d'un seul type, et le dossier « Commentaires » affiche le nombre de commentaires en attente de modération ([#550](https://github.com/counteraccro/natheo/issues/550))
- API v2 : `GET /api/v2/admin/modules` et `GET /api/v2/admin/menu_items` donnent les modules et les entrées du menu visibles par l'utilisateur de la session ([#550](https://github.com/counteraccro/natheo/issues/550))
- Design system du nouveau back-office pour les modules et les bundles : jetons des quatre thèmes en clair et en sombre, police Figtree, composants Vue importés depuis `@ui` (boutons, champs, formulaires avec les erreurs de validation de l'API et leur récapitulatif, modales, menus, onglets, alertes, notifications, grille de données avec recherche en deux temps et sélection multiple) et composants du layout ; catalogue sur `/admin/{locale}/design-system` en environnement dev ([#517](https://github.com/counteraccro/natheo/issues/517))

### Modifié

- Site public : l'utilisateur connecté passe par une session API (access token de 60 minutes, renouvelé automatiquement), révoquée à la déconnexion ([#505](https://github.com/counteraccro/natheo/issues/505), [#510](https://github.com/counteraccro/natheo/issues/510))
- Passage à Symfony 8.1.11
- Nouveau thème Natheo-Horizon, thème par défaut du site public, réécrit sur l'API v2 : nouvelle identité (logo en grille de blocs, violet, police Schibsted Grotesk), vrai mode sombre, accessibilité WCAG 2.2 AA, temps de lecture et barre de progression ; le site public n'appelle plus l'API v1 ([#464](https://github.com/counteraccro/natheo/issues/464))
- Natheo-Horizon : une page dont tous les blocs sont pleine largeur, sans listing, s'affiche dans une colonne de lecture centrée ([#525](https://github.com/counteraccro/natheo/issues/525))
- Options système : le texte du pied de page du site public se saisit dans chaque langue et s'affiche dans la langue de la page. La valeur est désormais stockée en JSON (`{"fr": "…", "en": "…"}`) dans `option_system.value`, la migration place le texte actuel sous la langue par défaut ; l'API v1 renvoie toujours une chaîne, celle de la langue par défaut ([#526](https://github.com/counteraccro/natheo/issues/526))
- Données de démonstration installées par `natheo:install` : le site vitrine de Nathéo, avec un vrai contenu en français, anglais et espagnol, toutes les dispositions de page, les onze types de menus, des commentaires dans chaque statut et des captures du CMS en images d'en-tête ([#521](https://github.com/counteraccro/natheo/issues/521))
- Le favicon reprend le logo en grille de blocs sur le site public, l'administration et l'installation ([#521](https://github.com/counteraccro/natheo/issues/521))
- Menus : l'aperçu de l'éditeur affiche le menu avec le thème du site public (en-tête, menu de côté ou pied de page), suit les modifications avant l'enregistrement, en largeur ordinateur ou mobile et en mode clair ou sombre. Un thème fournit son aperçu avec `menu_preview.html.twig`, à défaut celui de Natheo-Base est utilisé ([#533](https://github.com/counteraccro/natheo/issues/533))
- Site public : la version affichée en bas de page est celle du thème actif (paramètre `app.<thème>.version`), et non plus toujours celle de Natheo-Horizon ([#465](https://github.com/counteraccro/natheo/issues/465))
- Back-office : la sidebar est construite depuis les modules déclarés dans le code, sur autant de niveaux que nécessaire, et le bouton « Nouveau » de l'en-tête depuis leurs entrées de création ([#550](https://github.com/counteraccro/natheo/issues/550))
- Options système, tokens API, documentation de l'API, emails, traductions et aide de première connexion du tableau de bord sont ouverts aux administrateurs, et plus seulement au super administrateur ([#550](https://github.com/counteraccro/natheo/issues/550))
- Logo Nathéo sur les pages de connexion, de mot de passe et d'installation ([#517](https://github.com/counteraccro/natheo/issues/517))

### Déprécié

- API v1, supprimée en 3.0 : ses réponses portent les en-têtes `Deprecation`, `Sunset` et `Link` vers le guide de migration ([#474](https://github.com/counteraccro/natheo/issues/474))

### Corrigé

- Installation : les dossiers inscriptibles absents d'un clone neuf sont créés ([#501](https://github.com/counteraccro/natheo/issues/501))
- Options système : Natheo-Storm, qui provoquait une erreur 500 une fois choisi, est retiré de la liste des thèmes du site public ([#528](https://github.com/counteraccro/natheo/issues/528))
- Site public : le sélecteur de langue mène à la page affichée dans la langue choisie, et à l'accueil quand elle n'y est pas traduite ([#524](https://github.com/counteraccro/natheo/issues/524))
- Éditeur de page : la barre de statut indique « Aucune modification » à l'ouverture au lieu de « Modifications non sauvegardées », dans la langue de l'interface ([#523](https://github.com/counteraccro/natheo/issues/523))
- Back-office : la confirmation avant de quitter une page ne s'affiche plus après une recherche, un filtre, un lien ouvert dans un nouvel onglet ou sur les pages de connexion ; elle couvre désormais le retour du navigateur, le rechargement, la fermeture de l'onglet et les modifications sans saisie (glisser-déposer) ([#542](https://github.com/counteraccro/natheo/issues/542))
- Recherche globale : un contributeur ne trouve plus les comptes du back-office, réservés au super administrateur. Chaque onglet suit le module de ses éléments ([#550](https://github.com/counteraccro/natheo/issues/550))

### Sécurité

- Sessions API d'un utilisateur révoquées au changement de mot de passe et à la désactivation du compte ([#505](https://github.com/counteraccro/natheo/issues/505))

## [2.0.1] - 2026-11-07

### Corrigé

- Liens du README vers la documentation d'installation, de configuration de la base de données et d'installation en production

## [2.0.0] - 2026-11-07

### Rupture

- Nouveau schéma de base de données : pas de migration automatique depuis la V1, une nouvelle installation est nécessaire
- PHP 8.4 minimum, MySQL 8.2, MariaDB 10.11 ou PostgreSQL 15.2

### Ajouté

- Support de PostgreSQL à côté de MySQL / MariaDB, avec des migrations Doctrine dédiées à chaque plateforme
- Mise à jour du schéma par migrations Doctrine à partir de cette version
- Interface entièrement traduite en anglais et en espagnol, clés de traduction contrôlées par les tests

### Modifié

- Interface d'administration harmonisée sur tous les écrans : en-têtes de page, boutons, onglets, barre latérale et messages
- Code réorganisé par rôle (services, enums, factories…), dossiers `Utils` supprimés

### Supprimé

- Bootstrap, remplacé par Tailwind CSS

### Sécurité

- Audit de tous les modules (contenus, système, outils, API, site public) : échappement des grilles, protection CSRF des actions AJAX, droits par contenu ([GHSA-m6wx-pj44-98rm](https://github.com/counteraccro/natheo/security/advisories/GHSA-m6wx-pj44-98rm), [GHSA-6qw2-2pv6-qjx7](https://github.com/counteraccro/natheo/security/advisories/GHSA-6qw2-2pv6-qjx7))

## [2.0.0-beta.2] - 2026-10-13

### Corrigé

- Corrections issues de l'audit des modules : médiathèque, pages, recherche globale, options avancées, base de données, utilisateurs, options système, barre latérale, traductions, emails, logs, tokens d'API, éditeur, API et site public

### Modifié

- Passage à Symfony 8.1.8

## [2.0.0-beta.1] - 2026-09-06

### Ajouté

- Nouveau back-office en Tailwind CSS 4 (Flowbite), avec mode sombre et thèmes de couleur
- Composants Vue 3 de l'administration écrits en TypeScript
- Tableau de bord avec statistiques et blocs configurables
- Nouvel assistant d'installation et commandes console (`natheo:install`, `natheo:translations:update`)

### Modifié

- Refonte de tous les écrans de l'administration : authentification, tags, options système, recherche globale, utilisateurs, notifications, tokens d'API, traductions, logs, options avancées, SQL, base de données, emails, FAQ, médiathèque, commentaires, menus et pages
- Passage à Symfony 8.1 et Vite 8, typage strict sur tout le projet

### Corrigé

- Compatibilité MariaDB et affichage selon les rôles (boutons de l'en-tête, blocs du tableau de bord)

### Sécurité

- Suppression sécurisée de la base, corrections de path traversal et d'injection SQL sur le tri

## [1.0.0] - 2025-10-07

### Modifié

- Passage de Webpack Encore à Vite, code formaté avec Prettier
- Passage à Symfony 7.3.4

### Corrigé

- Modale de l'éditeur avec plusieurs éditeurs sur une page, test d'envoi d'email

## [1.0.0-beta.2] - 2025-09-10

### Ajouté

- Thème public Natheo-Horizon : structure, en-tête, menus horizontal et vertical, pied de page, fil d'Ariane, SEO, sitemap, contenu des pages, compte utilisateur et commentaires
- Nouvelles options système

### Corrigé

- API : le menu par défaut était renvoyé même lorsqu'il ne devait pas l'être

## [1.0.0-beta.1] - 2025-06-18

Première version publique (republiée en `1.0.0-beta.1.0.1` pour corriger le numéro de version affiché).

### Ajouté

- Gestion des pages (contenus, prévisualisation), des menus, des FAQ, des tags, des commentaires et de la médiathèque
- Administration : utilisateurs, options système et avancées, traductions, logs, emails, gestionnaire SQL et de base de données
- API v1 (pages, menus, commentaires, options système) avec tokens d'API
- Installation du CMS, compatibilité MySQL, surcharge des contrôleurs
- Tests unitaires

[Non publié]: https://github.com/counteraccro/natheo/compare/v2.0.1...HEAD
[2.0.1]: https://github.com/counteraccro/natheo/compare/v2.0.0...v2.0.1
[2.0.0]: https://github.com/counteraccro/natheo/compare/v2.0.0-beta.2...v2.0.0
[2.0.0-beta.2]: https://github.com/counteraccro/natheo/compare/v2.0.0-beta.1...v2.0.0-beta.2
[2.0.0-beta.1]: https://github.com/counteraccro/natheo/compare/v1.0.0...v2.0.0-beta.1
[1.0.0]: https://github.com/counteraccro/natheo/compare/v1.0.0-beta.2...v1.0.0
[1.0.0-beta.2]: https://github.com/counteraccro/natheo/compare/v1.0.0-beta.1...v1.0.0-beta.2
[1.0.0-beta.1]: https://github.com/counteraccro/natheo/releases/tag/v1.0.0-beta.1
