# Projet Natheo

Le projet Natheo est un projet PHP pour développer un CMS avec Symfony

Consulter la [documentation](https://counteraccro.github.io/natheo.doc/) pour plus d'information

# Installation
Étape 1 : cloner le dépôt GIT

```https://github.com/counteraccro/natheo.git```

Étape 2 : Installer Symfony

```composer update```

Étape 3 : installer la base de données

```php bin/console doctrine:database:create```

Étape 4 : récupération des tables de la base de données

```php bin/console doctrine:schema:create```

Étape 5: installation des fixtures

```php bin/console doctrine:fixture:load```

# Accès au site
Sur votre environnement de développement
* Créer un virtual host qui pointe vers le dossier suivant : ```[path-complet-vers-mon-dossier]\www\natheo\public```
* Cliquez sur le lien ```http://[mon-virtual-host]/admin/fr/dashboard/index```
* Authentifier vous avec le login/mot de passe suivant : ```superadmin@natheo.com/superadmin@natheo.com```

# Commande : 

Lancer les scrypts en async : ```php bin/console messenger:consume async -vv```

Génération des traductions (fr|en|es) : ```php bin/console translation:extract --force --format=yaml en```

Chargement des fixtures : ```php bin/console doctrine:fixtures:load```

Compilations du JS : ```yarn encore dev -- watch```


[![Anurag's GitHub stats](https://github-readme-stats.vercel.app/api?username=counteraccro)](https://github.com/anuraghazra/github-readme-stats)

