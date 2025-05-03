# Étape de build
FROM composer:2 AS composer

WORKDIR /app
COPY composer.* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Image finale
FROM php:8.2-fpm-alpine

# Installation des dépendances système
RUN apk add --no-cache \
    postgresql-dev \
    nginx \
    supervisor \
    && docker-php-ext-install pdo_pgsql

# Configuration de PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Configuration de Nginx
COPY nginx.conf /etc/nginx/http.d/default.conf

# Configuration de Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copie des fichiers de l'application
COPY --from=composer /app /var/www/html
RUN chown -R www-data:www-data /var/www/html/var

# Script de démarrage
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80

CMD ["/usr/local/bin/start.sh"] 