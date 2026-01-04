# Projet Natheo

Le projet Natheo est un projet PHP pour développer un CMS avec Symfony

Consulter la [documentation](https://counteraccro.github.io/natheo.doc/) pour plus d'information

# Pré-requis

Environnement obligatoire pour faire tourner le CMS :

- PHP 8.3 ou +
- Base de données prise en charge :
    - PostgresSql 15.2 ou +
    - Mysql 8.2 ou +
- Yarn 1.22.19 ou +
- Composer 2.7.7 ou +

# Information

Par défaut la base de données utilisé est Mysql.
Pour utiliser une autre base de données, utiliser [la procédure suivante](https://counteraccro.github.io/natheo.doc/Docs/Installation/bdd.html)

# Installation

> Attention, la procédure actuelle est prévue pour une installation en mode développement
>
> Si vous souhaitez faire une installation via l'installeur, [consultez cette documentation](https://counteraccro.github.io/natheo.doc/Docs/Installation/install-prod.html)

#### Étape 1 : cloner le dépôt GIT

`https://github.com/counteraccro/natheo.git`

#### Étape 2 : Installer Symfony

`composer update`

#### Étape 3 : Configuration .env

Créer une copie du fichier `.env` en `.env.local`

- Mettre la valeur `dev` à `APP_ENV`
- Mettre la valeur `1` à `APP_DEBUG`
- Mettre la valeur `[nom-de-votre-bdd]` à `NATHEO_SCHEMA`

#### Étape 4 : Installation de natheo CMS

Cette commande permet de passer les étapes 5,6,7

`php bin/console natheo:install`

#### Étape 5 : installer la base de données

_A faire si vous souhaitez faire une installation point par point_

`php bin/console doctrine:database:create`

#### Étape 6 : récupération des tables de la base de données

_A faire si vous souhaitez faire une installation point par point_

`php bin/console doctrine:schema:create`

#### Étape 7 : installation des fixtures

_A faire si vous souhaitez faire une installation point par point_

`php bin/console doctrine:fixture:load`

#### Étape 8 : Génération des assets

`yarn encore dev -- watch`

# Accès au site

Sur votre environnement de développement

- Créer un virtual host qui pointe vers le dossier suivant : `[path-complet-vers-mon-dossier]\www\natheo\public`
- Cliquez sur le lien `http://[mon-virtual-host]/admin/fr/dashboard/index`
- Authentifier vous avec le login/mot de passe suivant : `superadmin@natheo.com/superadmin@natheo.com`

# Commande :

Installation générale du CMS : `php bin/console natheo:install`

Lancer les scripts en async : `php bin/console messenger:consume async -vv`

Génération des traductions (fr ou en ou es) : `php bin/console translation:extract --force --format=yaml en`

Chargement des fixtures : `php bin/console doctrine:fixtures:load`

Compilations du JS : `yarn encore dev -- watch`

Lancement des tests unitaires : `bin/phpunit `


# Contributeurs

|                                 ----                                 | Nos contributeurs                                                                                                                                                         |
|:--------------------------------------------------------------------:|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
|    ![image](https://avatars.githubusercontent.com/u/3640049?v=4)     | [Counteraccro](https://github.com/counteraccro) <br /> Fondateur, LeadDev et principal développeur                                                                        |
| ![image](https://avatars.githubusercontent.com/u/139382475?s=48&v=4) | [MaxenceMahieux](https://github.com/MaxenceMahieux) <br /> Co-fondateur et Admin System <br /> Autre projet : [Flaase](https://github.com/MaxenceMahieux/flaase-cli-rust) |
