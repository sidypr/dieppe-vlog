# Dieppe Vlog

[![CI/CD](https://github.com/votre-username/dieppe-vlog/actions/workflows/main.yml/badge.svg)](https://github.com/votre-username/dieppe-vlog/actions)
[![Deploy to Render](https://img.shields.io/badge/deploy%20to-render-purple)](https://render.com)

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

Le projet est configuré pour être déployé automatiquement sur Render.com à chaque push sur la branche main.

## Développement

1. Créez une nouvelle branche pour votre fonctionnalité
```bash
git checkout -b feature/ma-fonctionnalite
```

2. Committez vos changements
```bash
git add .
git commit -m "feat: ajout de ma fonctionnalité"
```

3. Poussez et créez une Pull Request
```bash
git push origin feature/ma-fonctionnalite
```

## Licence

MIT 