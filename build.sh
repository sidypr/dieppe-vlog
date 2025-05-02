#!/usr/bin/env bash

# Mise à jour des paquets
apt-get update
apt-get install -y php8.2-cli php8.2-common php8.2-curl php8.2-xml php8.2-zip php8.2-pgsql

# Installation de Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Installation des dépendances
composer install --no-interaction --prefer-dist --optimize-autoloader

# Configuration de l'application
php bin/console cache:clear
php bin/console doctrine:migrations:migrate --no-interaction 