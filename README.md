# Ma Beauté Zen

---

## Description

Projet Symfony propulsé par PHP 8.4+, avec gestion avancée du backend et des assets front-end via NPM.

---

## Fonctionnalités

- 🔗 **Gestion des clients** : Suivi des informations clients.
- 📊 **Administration** : Gestion centralisée des données (produits, commandes, utilisateurs, etc.).
- ✉️ **Envoi d'email** : Intégration avec Symfony Mailer pour des notifications automatiques.
- 📦 **Catalogue de produits** : Consultation des produits avec leur prix, leurs taxes et autres détails.
- 📃 **Taux de TVA** : Gestion simplifiée des différents taux de TVA français directement dans l'application.

---

## 🚀 Stack technique

- **Symfony 7.2**
- **PHP >=8.4**
- **Doctrine ORM & Migrations**
- **Twig** pour le templating
- **EasyAdmin** pour un back-office rapide
- **Monolog** pour la gestion des logs
- **AMPHP** pour les besoins asynchrones
- **Tattali Calendar Bundle** pour la gestion de calendrier
- **Bootstrap Icons**
- **Gestion des assets** avec NPM
- **Stimulus, UX Turbo, Asset Mapper** (Front dynamique et moderne)
- **Mailer, Notifier** (Emails/SMS)

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

## 📦 Dépendances principales

### PHP (extraits des principaux bundles)

- `symfony/framework-bundle`
- `doctrine/orm`
- `doctrine/doctrine-bundle`
- `easycorp/easyadmin-bundle`
- `symfony/twig-bundle`, `twig/twig`
- `symfony/mailer`, `symfony/notifier`
- `symfony/security-bundle`, `symfony/password-hasher`
- `symfony/validator`, `symfony/form`
- `symfonycasts/reset-password-bundle`
- `tattali/calendar-bundle`
- `phpstan/phpdoc-parser`
- (et beaucoup d'autres, voir composer.json pour la liste complète)

### Packages de développement

- `phpunit/phpunit`
- `symfony/maker-bundle`
- `doctrine/doctrine-fixtures-bundle`
- `symfony/web-profiler-bundle`
- Outils pour tests, debug, fixtures, etc.

### JavaScript/Front (via NPM)

- Développement et gestion des assets front avec NPM
- Utilisation de packages JS pour le front (ajoute ici la liste si besoin, ex: Bootstrap, StimulusJS…)

---

## ⚡ Installation

1. **Cloner le projet**
   ```bash
   git clone <repo>
   cd <dossier>
   ```

2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```

3. **Installer les dépendances JavaScript**
   ```bash
   npm install
   ```

4. **Configurer l'environnement**
   ```bash
   cp .env .env.local
   # Adapter selon votre configuration (base SQLite par défaut)
   ```

5. **Préparer la base de données**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

6. **Charger les fixtures (données de test)**
   ```bash
   composer db_fixtures_reload
   ```

7. **Démarrer le serveur Symfony**
   ```bash
   composer start
   # ou
   symfony server:start
   ```

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
- **Purger la base de données**
  ```bash
  composer db_purge
  ```

# ✨ Personnalisation rapide du Calendar Bundle

## 1️⃣  Personnaliser les événements affichés

- **Fichier à modifier :**  
  `src/EventSubscriber/CalendarSubscriber.php`
- **Que faire :**
    - Modifier la requête pour afficher les bons événements selon votre logique.
    - Personnaliser les objets `Event` : titre, dates, couleurs, URL, etc.

---

## 🎨 Personnaliser le style du calendrier (CSS)

- **Fichier à modifier :**  
  `templates/_calendar.html.twig`
- **Que faire :**
    - Modifier la section `<style>` pour adapter le design : couleurs, boutons, etc.

---

## ⚙️ Modifier le comportement ou les options du calendrier (JavaScript/FullCalendar)

- **Fichier à modifier :**  
  `templates/_calendar.html.twig` (dans le bloc `<script>`)
- **Que faire :**
    - Changer les options FullCalendar : vue, horaires, toolbar, langue, etc.
    - Ajouter des boutons personnalisés (ex : "Ajouter un événement").

---

## 🔗 Configurer la source des événements

- **Fichier à modifier :**  
  `templates/_calendar.html.twig` (option `eventSources`)
- **Que faire :**
    - Modifier l’URL de récupération des événements et les paramètres envoyés.

---

## 🧩 Gérer l’interactivité et ajouter des filtres

- **Fichiers à modifier :**
    - `templates/_calendar.html.twig` (JavaScript)
    - Backend (`CalendarSubscriber`, controller, etc.)
- **Que faire :**
    - Ajouter des filtres côté JS et les transmettre au backend, puis adapter le backend pour les utiliser.

---

## 📝 Résumé

| Personnalisation              | Fichier à modifier                        |
|------------------------------|-------------------------------------------|
| 🗓️ Événements affichés        | `CalendarSubscriber.php` (backend)        |
| 🎨 Style visuel (CSS)         | `_calendar.html.twig` (section `<style>`) |
| ⚙️ Options et comportement    | `_calendar.html.twig` (section `<script>`)|
| 🔗 Source/URL des événements  | `_calendar.html.twig` (`eventSources`)    |
| 🧩 Filtres et interactivité   | `_calendar.html.twig` + backend           |

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