#!/bin/bash

# Installation des dépendances
composer install --no-dev --optimize-autoloader

# Création des répertoires nécessaires
mkdir -p public/uploads
chmod -R 777 public/uploads
chmod -R 777 var

# Configuration de la base de données
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction

# Nettoyage du cache
php bin/console cache:clear --env=prod 