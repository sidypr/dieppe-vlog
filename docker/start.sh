#!/bin/sh

# Création des répertoires nécessaires
mkdir -p /var/www/html/var/cache
mkdir -p /var/www/html/var/log
mkdir -p /var/www/html/public/uploads

# Attribution des permissions
chown -R www-data:www-data /var/www/html/var
chown -R www-data:www-data /var/www/html/public/uploads

# Nettoyage du cache et migration de la base de données
php /var/www/html/bin/console cache:clear --env=prod
php /var/www/html/bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Démarrage de Supervisor
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf 