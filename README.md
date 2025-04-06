# Ma Beauté Zen

## Description

**Ma Beauté Zen** est une application web développée avec le framework Symfony. Elle est conçue pour répondre aux besoins de la gestion clients, de la publicité et de l'administration de la société **Ma Beauté Zen**.

## Fonctionnalités

- 🔗 **Gestion des clients** : Suivi des informations clients.
- 📊 **Administration** : Gestion centralisée des données (produits, commandes, utilisateurs, etc.).
- ✉️ **Envoi d'email** : Intégration avec Symfony Mailer pour des notifications automatiques.
- 📦 **Catalogue de produits** : Consultation des produits avec leur prix, leurs taxes et autres détails.
- 📃 **Taux de TVA** : Gestion simplifiée des différents taux de TVA français directement dans l'application.

---

## Prérequis

Avant de commencer, assurez-vous que votre environnement respecte les prérequis suivants :

- **PHP** : >= 8.4
- **Extensions PHP** :
    - `ctype`
    - `iconv`
- **Composer** : Le gestionnaire de dépendances PHP
- **Serveur Web** tel qu'Apache ou Nginx.
- **Base de données** : SQLite (préconfigurée, mais adaptable à un autre SGBD).

---

## Installation

**Étapes pour démarrer le projet :**

1. **Clonez le dépôt :**
   ```bash
   git clone https://github.com/Alker51/ma_beaute_zen.git
   cd ma_beaute_zen
   ```

2. **Installez les dépendances :**
   ```bash
   composer install
   ```

3. **Configurer les variables d'environnement :**
    - Copiez le fichier `.env` par défaut :
      ```bash
      cp .env .env.local
      ```
    - Configurez les variables nécessaires, comme la connexion à la base de données (optionnel si vous utilisez SQLite).

4. **Configurer la base de données :**
    - Créez votre base de données et le schéma à partir des entités définies :
      ```bash
      php bin/console doctrine:database:create
      php bin/console doctrine:schema:update --force
      ```
    - (Optionnel) Chargez les fixtures pour des données initiales :
      ```bash
      php bin/console doctrine:fixtures:load
      ```

5. **Lancez le serveur de développement :**
   ```bash
   composer start
   ```
   Accédez à l'application à l'URL : [http://localhost:8000](http://localhost:8000).

---

## Utilisateur Administrateur par Défaut

Lors de l'initialisation de la base de données via les fixtures, un utilisateur administrateur est créé automatiquement avec les informations suivantes :

- **Email** : `admin@example.com`
- **Mot de passe** : `admin123`

### Attention :
Il est fortement recommandé de modifier ces informations dans un environnement de production pour des raisons de sécurité.

---

## Commands Importantes

### Scripts Composer

Utilisez les commandes suivantes avec `composer` pour gérer le projet rapidement.

- **Démarrer le serveur** :
  ```bash
  composer start
  ```
- **Arrêter le serveur** :
  ```bash
  composer stop
  ```
- **Recharger la base de données complète** *(base vide + fixtures)* :
  ```bash
  composer db_total_reload
  ```
- **Recharger uniquement les fixtures** :
  ```bash
  composer db_fixtures_reload
  ```
- **Nettoyer le cache** :
  ```bash
  composer cache:clear
  ```

---

## Structure des dossiers principaux

Votre projet est structuré comme suit :

```bash
├── src/
│   ├── Controller/        # Contrôleurs Symfony
│   ├── Entity/            # Entités Doctrine ORM
│   ├── Repository/        # Requêtes personnalisées Doctrine
│   ├── Form/              # Formulaires Symfony
│   └── DataFixtures/      # Données de test pour remplissage
├── config/                # Configuration de l'application (base de données, services, etc.)
├── public/                # Racine de l'application (accessible via le navigateur)
├── templates/             # Templates Twig
├── migrations/            # Migrations pour la base de données
├── var/                   # Caches et données temporaires
└── tests/                 # Tests (unitaires et fonctionnels)
```

---

## Tests

### Lancer les tests

Le projet inclut PHPUnit pour écrire et exécuter des tests.

1. **Installez PHPUnit (si ce n'est pas encore fait)** :
   ```bash
   composer install --dev
   ```

2. **Exécutez les tests** :
   ```bash
   php bin/phpunit
   ```

---

## Dépendances principales

Voici la liste des principales librairies utilisées par le projet :

### Dépendances applicatives :

- **Symfony** : Framework PHP de haute performance.
- **Doctrine ORM** : Gestion des entités et de la base de données.
- **Twig** : Moteur de templates léger et performant.
- **Symfony Mailer** : Envoi d'e-mails.

### Dépendances de développement :

- **PHPUnit** : Tests unitaires.
- **Symfony MakerBundle** : Générateurs de code pour Symfony.
- **Doctrine Fixtures** : Initialiser ou recharger les données.

---

## Auteur

Ce projet est réalisé par **Rémy Robin** pour **Fragment Web**.

- **Contact** : robinremy51600@gmail.com

<!-- **Site** : [https://www.fragment-web.com](https://www.fragment-web.com) -->

---

## Licence

Ce projet est sous **licence propriétaire**. Il ne peut être utilisé, modifié et partagé sans une autorisation explicite de **Fragment Web**.