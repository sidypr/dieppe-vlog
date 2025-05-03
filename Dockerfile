FROM php:8.2-fpm-alpine

# Installation des dépendances système
RUN apk add --no-cache \
    postgresql-dev \
    git \
    zip \
    unzip

# Installation des extensions PHP
RUN docker-php-ext-install pdo_pgsql

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration du répertoire de travail
WORKDIR /var/www/html

# Copie des fichiers du projet
COPY . .

# Installation des dépendances
RUN composer install --no-dev --optimize-autoloader

# Configuration des permissions
RUN chown -R www-data:www-data var

# Exposition du port
EXPOSE 9000

# Démarrage de PHP-FPM
CMD ["php-fpm"] 