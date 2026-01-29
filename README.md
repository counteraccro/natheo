<div align="center">

# Nathéo CMS

[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php)](https://www.php.net/)
[![Symfony](https://img.shields.io/badge/Symfony-8.x-000000?style=flat-square&logo=symfony)](https://symfony.com/)
[![License](https://img.shields.io/badge/License-GNU-green?style=flat-square)](LICENSE)
[![Documentation](https://img.shields.io/badge/Docs-En%20ligne-blue?style=flat-square)](https://counteraccro.github.io/natheo.doc/)

**Un CMS moderne et performant développé avec Symfony**

[Documentation](https://counteraccro.github.io/natheo.doc/) · [Signaler un bug](https://github.com/counteraccro/natheo/issues) · [Demander une fonctionnalité](https://github.com/counteraccro/natheo/issues)

</div>

---

## 📋 Table des matières

- [À propos](#-à-propos)
- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Configuration](#️-configuration)
- [Utilisation](#-utilisation)
- [Commandes disponibles](#-commandes-disponibles)
- [Tests](#-tests)
- [Documentation](#-documentation)
- [Contribution](#-contribution)
- [Contributeurs](#-contributeurs)
- [Licence](#-licence)

---

## 🚀 À propos

Nathéo est un système de gestion de contenu (CMS) moderne développé avec Symfony, offrant une solution flexible et performante pour la création et la gestion de sites web. Le projet privilégie la modularité, la sécurité et l'expérience utilisateur.

### Fonctionnalités principales

- 🎨 Interface d'administration moderne et responsive
- 🔒 Système d'authentification et de permissions robuste
- 📝 Gestion de contenu flexible
- 🌍 Support multilingue (FR, EN, ES)
- ⚡ Performance optimisée
- 🎯 Architecture modulaire

---

## ⚙️ Prérequis

Avant de commencer, assurez-vous d'avoir installé les éléments suivants :

| Composant      | Version requise | Notes                           |
|----------------|----------------|---------------------------------|
| **PHP**        | 8.3 ou supérieur | Extensions recommandées ci-dessous |
| **Composer**   | 2.7.7 ou supérieur | Gestionnaire de dépendances PHP |
| **Yarn**       | 1.22.19 ou supérieur | Gestionnaire de paquets JavaScript |
| **Base de données** | Voir ci-dessous | MySQL ou PostgreSQL |

### Base de données supportées

- **PostgreSQL** : 15.2 ou supérieur
- **MySQL** : 8.2 ou supérieur *(par défaut)*

> **📌 Note** : Pour utiliser PostgreSQL ou une autre base de données, consultez [la documentation de configuration](https://counteraccro.github.io/natheo.doc/Docs/Installation/bdd.html).

### Extensions PHP recommandées
```
ext-ctype, ext-iconv, ext-pdo, ext-pdo_mysql, ext-json, ext-mbstring
```

---

## 📦 Installation

### Installation rapide (développement)

> **⚠️ Attention** : Cette procédure est prévue pour un environnement de développement.  
> Pour une installation en production, consultez [la documentation d'installation production](https://counteraccro.github.io/natheo.doc/Docs/Installation/install-prod.html).

#### 1. Cloner le dépôt
```bash
git clone https://github.com/counteraccro/natheo.git
cd natheo
```

#### 2. Installer les dépendances
```bash
composer install
```

#### 3. Configurer l'environnement

Créez une copie du fichier `.env` en `.env.local` :
```bash
cp .env .env.local
```

Modifiez les variables suivantes dans `.env.local` :
```env
APP_ENV=dev
APP_DEBUG=1
NATHEO_SCHEMA=natheo_dev
DATABASE_URL="mysql://user:password@127.0.0.1:3306/natheo_dev?serverVersion=8.2"
```

#### 4. Installation automatique du CMS

Cette commande exécute automatiquement les étapes 5, 6 et 7 :
```bash
php bin/console natheo:install
```

<details>
<summary><strong>Installation manuelle (optionnel)</strong></summary>

Si vous préférez effectuer l'installation étape par étape :

##### 5. Créer la base de données
```bash
php bin/console doctrine:database:create
```

##### 6. Créer le schéma de base de données
```bash
php bin/console doctrine:schema:create
```

##### 7. Charger les données de test
```bash
php bin/console doctrine:fixtures:load
```

</details>

#### 8. Compiler les assets
```bash
yarn install
yarn encore dev --watch
```

---

## 🛠️ Configuration

### Configuration du Virtual Host

Pour accéder au CMS en développement :

1. Créez un virtual host pointant vers : `[chemin-vers-natheo]/public`
2. Exemple de configuration Apache :
```apache
<VirtualHost *:80>
    ServerName natheo.local
    DocumentRoot "[chemin-vers-natheo]/public"
    
    <Directory "[chemin-vers-natheo]/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

3. Ajoutez l'entrée dans votre fichier `hosts` :
```
127.0.0.1    natheo.local
```

---

## 💻 Utilisation

### Accès à l'interface d'administration

Une fois l'installation terminée :

1. Accédez à : `http://natheo.local/admin/fr/dashboard/index`
2. Connectez-vous avec les identifiants par défaut :
  - **Email** : `superadmin@natheo.com`
  - **Mot de passe** : `superadmin@natheo.com`

> **🔐 Important** : Changez immédiatement ces identifiants en production !

---

## 📝 Commandes disponibles

### Commandes principales

| Commande | Description |
|----------|-------------|
| `php bin/console natheo:install` | Installation complète du CMS |
| `php bin/console messenger:consume async -vv` | Traitement des tâches asynchrones |
| `php bin/console doctrine:fixtures:load` | Chargement des données de test |

### Gestion des traductions

Générer/extraire les traductions pour une langue :
```bash
# Français
php bin/console translation:extract --force --format=yaml fr

# Anglais
php bin/console translation:extract --force --format=yaml en

# Espagnol
php bin/console translation:extract --force --format=yaml es
```

### Compilation des assets
```bash
# Mode développement avec watch
yarn encore dev --watch

# Mode développement (compilation unique)
yarn encore dev

# Mode production
yarn encore production
```

---

## 🧪 Tests

Lancer la suite de tests unitaires :
```bash
bin/phpunit
```

Pour plus d'informations sur les tests, consultez la [documentation des tests](https://counteraccro.github.io/natheo.doc/Docs/Tests/).

---

## 📚 Documentation

La documentation complète est disponible en ligne :

- **Documentation officielle** : [https://counteraccro.github.io/natheo.doc/](https://counteraccro.github.io/natheo.doc/)
- **Guide d'installation** : [Installation](https://counteraccro.github.io/natheo.doc/Docs/Installation/)
- **Configuration de la base de données** : [Configuration BDD](https://counteraccro.github.io/natheo.doc/Docs/Installation/bdd.html)
- **Installation en production** : [Mode production](https://counteraccro.github.io/natheo.doc/Docs/Installation/install-prod.html)

---

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à :

1. 🍴 Fork le projet
2. 🔨 Créer une branche pour votre fonctionnalité (`git checkout -b feature/AmazingFeature`)
3. ✅ Commit vos changements (`git commit -m 'Add some AmazingFeature'`)
4. 📤 Push vers la branche (`git push origin feature/AmazingFeature`)
5. 🎉 Ouvrir une Pull Request

---

## 👥 Contributeurs

<table>
  <tr>
    <td align="center">
      <a href="https://github.com/counteraccro">
        <img src="https://avatars.githubusercontent.com/u/3640049?v=4" width="100px;" alt="Counteraccro"/><br />
        <sub><b>Counteraccro</b></sub>
      </a><br />
      <sub>Fondateur & Lead Developer</sub>
    </td>
    <td align="center">
      <a href="https://github.com/MaxenceMahieux">
        <img src="https://avatars.githubusercontent.com/u/139382475?v=4" width="100px;" alt="MaxenceMahieux"/><br />
        <sub><b>Maxence Mahieux</b></sub>
      </a><br />
      <sub>Co-fondateur & Admin Système</sub><br />
      <sub><a href="https://github.com/MaxenceMahieux/flaase-cli-rust">Projet : Flaase CLI</a></sub>
    </td>
  </tr>
</table>

---

## 📄 Licence

Ce projet est sous licence GNU-V3. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

<div align="center">

**Développé avec ❤️ par l'équipe Nathéo**

[⬆ Retour en haut](#nathéo-cms)

</div>