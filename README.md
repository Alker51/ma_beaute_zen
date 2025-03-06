# Ma Beauté Zen

## Description

Ce projet est une application développée avec Symfony, conçue pour les clients, la publicité et l'administration de la société Ma beauté zen.

## Auteur

- **Rémy Robin**

## Licence

Ce projet est sous licence propriétaire.

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
   git clone <URL_DU_DEPOT>
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
   symfony server\:start
   ```

## Scripts Composer

- **Démarrer le serveur** : `composer start`
- **Arrêter le serveur** : `composer stop`
- **Nettoyer le cache** : `composer cache:clear`