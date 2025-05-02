# Dieppe Vlog

Une plateforme de streaming vidéo développée avec Symfony 6.

## Fonctionnalités

- 🎥 Lecture de vidéos
- 👥 Gestion des utilisateurs
- 💬 Système de commentaires
- ❤️ Système de likes
- 🎨 Interface responsive

## Installation

```bash
# Cloner le projet
git clone https://github.com/votre-username/dieppe-vlog.git
cd dieppe-vlog

# Installer les dépendances
composer install

# Configurer la base de données dans .env.local
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"

# Créer la base de données
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Démarrer le serveur
symfony serve
```

## Déploiement

Le projet est configuré pour être déployé sur Render.com.

## Licence

MIT 