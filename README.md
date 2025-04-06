# Ma Beauté Zen

## Description

Ce projet est une application développée avec Symfony, conçue pour les clients, la publicité et l'administration de la société Ma beauté zen.

## Auteur

- **Rémy Robin**

## Licence

Ce projet est sous licence propriétaire de **Fragment Web**.

## Configuration Requise

- PHP >= 8.4
- Extensions PHP : ctype, iconv

## Dépendances

### Principales dépendances

- **Symfony Components** : `asset`, `console`, `form`, `framework-bundle`, `http-client`, `mailer`, `security-bundle`, `twig-bundle`, etc.
- **Doctrine** : `dbal`, `orm`, `doctrine-bundle`, `doctrine-migrations-bundle`
- **Autres** : `amphp/http-client`, `phpdocumentor/reflection-docblock`, `phpstan/phpdoc-parser`, etc.

### Dépendances de Développement

- **PHPUnit** : `phpunit/phpunit`
- **Symfony Debugging** : `symfony/debug-bundle`, `symfony/web-profiler-bundle`
- **Autres** : `symfony/maker-bundle`, `symfony/phpunit-bridge`, etc.

## Installation

1. Clonez le dépôt :
   ```bash
   git clone https://github.com/Alker51/ma_beaute_zen.git
   cd ma_beaute_zen
   ```

2. Installez les dépendances avec Composer :
   ```bash
   composer install
   ```

3. Configurez votre environnement :

    - Copiez le fichier `.env` et configurez les variables d'environnement nécessaires.

4. Lancez le serveur de développement :
   ```bash
   composer start
   ```

5. Initialiser la base de donnée :
   ```bash
   composer db_total_reload
   ```

## Scripts Composer

Voici les différents scripts définis dans le fichier `composer.json` pour gérer des actions courantes dans le projet :

- **Démarrer le serveur** : `composer start`
- **Arrêter le serveur** : `composer stop`
- **Nettoyer le cache** : `composer cache:clear`
- **Rechargement complet de la base de données** : `composer db_total_reload`
   - Supprime la base de données existante, recrée une nouvelle base de données, applique le schéma et charge les fixtures.
- **Recharger uniquement les fixtures** : `composer db_fixtures_reload`
   - Recharge uniquement les données des fixtures sans toucher au schéma ou à la base de données.
- **Supprimer la base de données** : `composer db_purge`
   - Supprime complètement la base de données pour un nettoyage rapide.

### Exemple d'utilisation

Pour effectuer un rechargement complet de la base de données (utile en développement) :

```bash
   composer db_total_reload
```

Si vous souhaitez simplement recharger les fixtures, utilisez :

```bash
   composer db_fixtures_reload
```


## Utilisateur Administrateur par Défaut

Lors de l'initialisation de la base de données via les fixtures, un utilisateur administrateur est créé automatiquement avec les informations suivantes :

- **Email** : `admin@example.com`
- **Mot de passe** : `admin123`

### Attention :
Il est fortement recommandé de modifier ces informations dans un environnement de production pour des raisons de sécurité.