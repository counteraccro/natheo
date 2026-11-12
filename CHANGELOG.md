# Journal des modifications

Les changements notables de Nathéo CMS sont consignés dans ce fichier.

Le format suit [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/), avec une rubrique **Rupture** en plus pour les changements incompatibles, et le projet suit le [versionnage sémantique](https://semver.org/lang/fr/).

## [Non publié]

### Rupture

- Authentification des utilisateurs par l'API en sessions : les anciens tokens utilisateur sont invalidés par la migration, le token renvoyé par `POST /api/v1/authentication/user` est valable 15 minutes ([#505](https://github.com/counteraccro/natheo/issues/505))
- Option système de durée des sessions API (`OS_API_TIME_VALIDATE_USER_TOKEN`) limitée à 1, 7 ou 30 jours, ramenée à 1 jour par la migration ([#505](https://github.com/counteraccro/natheo/issues/505))

### Ajouté

- Brouillon d'une page publiée : modifications enregistrées sans toucher la version en ligne, prévisualisées puis publiées ([#460](https://github.com/counteraccro/natheo/issues/460))
- Option système « Validation des pages » : les contributeurs soumettent leurs pages, un administrateur les valide ou les refuse ([#461](https://github.com/counteraccro/natheo/issues/461))
- Bloc « Pages à valider » sur le tableau de bord ([#489](https://github.com/counteraccro/natheo/issues/489))
- API v2 avec API Platform sous `/api/v2` : JSON-LD / Hydra ou JSON, erreurs `application/problem+json`, documentation Swagger sur `/api/v2/docs` quand l'API est ouverte ([#469](https://github.com/counteraccro/natheo/issues/469))
- Ressources de l'API v2 : pages, contenus et menus ([#470](https://github.com/counteraccro/natheo/issues/470)), commentaires ([#471](https://github.com/counteraccro/natheo/issues/471)), options système et sitemap ([#472](https://github.com/counteraccro/natheo/issues/472))
- Sessions API des utilisateurs : connexion, access token de 15 minutes dans `Authorization: Bearer`, renouvellement par refresh token, liste des sessions, déconnexion et `/me` ([#505](https://github.com/counteraccro/natheo/issues/505))

### Modifié

- Site public : l'utilisateur connecté passe par une session API (access token de 60 minutes, renouvelé automatiquement), révoquée à la déconnexion ([#505](https://github.com/counteraccro/natheo/issues/505), [#510](https://github.com/counteraccro/natheo/issues/510))
- Passage à Symfony 8.1.11

### Déprécié

- API v1, supprimée en 3.0 : ses réponses portent les en-têtes `Deprecation`, `Sunset` et `Link` vers le guide de migration ([#474](https://github.com/counteraccro/natheo/issues/474))

### Corrigé

- Installation : les dossiers inscriptibles absents d'un clone neuf sont créés ([#501](https://github.com/counteraccro/natheo/issues/501))

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
