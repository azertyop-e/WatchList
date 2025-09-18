# Application Laravel - Base de données de films

Une application Laravel moderne pour la gestion d'une base de données de films avec une interface utilisateur élégante.

## Prérequis

Avant de commencer, assurez-vous d'avoir installé :

- **Docker** (version 20.10 ou supérieure)
- **Docker Compose** (version 2.0 ou supérieure)
- **Git**

### Installation de Docker

#### Sur macOS
```bash
# Via Homebrew
brew install --cask docker

# Ou téléchargez Docker Desktop depuis https://www.docker.com/products/docker-desktop
```

#### Sur Linux (Ubuntu/Debian)
```bash
# Installation de Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Installation de Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

#### Sur Windows
Téléchargez et installez Docker Desktop depuis [docker.com](https://www.docker.com/products/docker-desktop)

## Installation rapide

### Méthode 1 : Script d'installation automatique

```bash
# Cloner le projet
git clone <url-du-repo>
cd laravel

# Rendre le script exécutable
chmod +x install.sh

# Lancer l'installation
./install.sh
```

### Méthode 2 : Installation manuelle

```bash
# 1. Cloner le projet
git clone <url-du-repo>
cd laravel

# 2. Configurer l'environnement
cp env.docker.example .env

# 3. Construire et démarrer les conteneurs
docker-compose build --no-cache
docker-compose up -d

# 4. Installer les dépendances PHP
docker-compose exec app composer install

# 5. Générer la clé d'application
docker-compose exec app php artisan key:generate

# 6. Exécuter les migrations
docker-compose exec app php artisan migrate

# 7. Nettoyer le cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
```

## Accès à l'application

Une fois l'installation terminée, vous pouvez accéder à :

- **Application Laravel** : http://localhost:8000
- **phpMyAdmin** : http://localhost:8080
  - Utilisateur : `root`
  - Mot de passe : `root_password`

## Commandes utiles

### Gestion des conteneurs

```bash
# Démarrer tous les services
docker-compose up -d

# Arrêter tous les services
docker-compose down

# Redémarrer les services
docker-compose restart

# Voir les logs en temps réel
docker-compose logs -f

# Voir les logs d'un service spécifique
docker-compose logs -f app
```

### Commandes Laravel

```bash
# Entrer dans le conteneur de l'application
docker-compose exec app bash

# Exécuter des commandes Artisan
docker-compose exec app php artisan migrate
docker-compose exec app php artisan make:controller NomController
docker-compose exec app php artisan route:list

# Nettoyer le cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear

# Exécuter les tests
docker-compose exec app php artisan test
```

### Gestion des dépendances

```bash
# Installer des packages Composer
docker-compose exec app composer require nom/package

# Installer des packages NPM
docker-compose exec app npm install nom-package

# Compiler les assets
docker-compose exec app npm run build
```

## Structure du projet

```
laravel/
├── app/                    # Code de l'application
│   ├── Http/Controllers/   # Contrôleurs
│   ├── Models/            # Modèles Eloquent
│   └── View/Components/   # Composants Blade
├── database/
│   ├── migrations/        # Migrations de base de données
│   └── seeders/          # Seeders pour les données de test
├── resources/
│   ├── views/            # Vues Blade
│   ├── css/              # Styles CSS
│   └── js/               # JavaScript
├── routes/               # Routes de l'application
├── docker/               # Configuration Docker
├── docker-compose.yml    # Configuration des services
└── Dockerfile           # Image Docker de l'application
```

## Base de données

L'application utilise MySQL 8.0 avec les tables suivantes :

- `users` - Utilisateurs
- `movies` - Films
- `actors` - Acteurs
- `collections` - Collections
- `production_companies` - Sociétés de production
- `production_countries` - Pays de production
- `spoken_languages` - Langues parlées
- `movie_roles` - Rôles dans les films

### Connexion à la base de données

- **Host** : `db` (dans Docker) ou `localhost:3307` (depuis l'extérieur)
- **Port** : `3306` (dans Docker) ou `3307` (depuis l'extérieur)
- **Base de données** : `laravel`
- **Utilisateur** : `laravel_user`
- **Mot de passe** : `password`

## Technologies utilisées

- **Backend** : Laravel 12, PHP 8.2
- **Frontend** : Blade, Tailwind CSS 4.0, Vite
- **Base de données** : MySQL 8.0
- **Serveur web** : Nginx
- **Containerisation** : Docker & Docker Compose
- **Outils de développement** : Laravel Debugbar, Laravel Pint

## Configuration

### Variables d'environnement importantes

Le fichier `.env` contient les configurations suivantes :

```env
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel_user
DB_PASSWORD=password
```

### Personnalisation

Vous pouvez modifier les configurations dans :
- `docker-compose.yml` pour les services Docker
- `docker/nginx/default.conf` pour la configuration Nginx
- `docker/php/local.ini` pour la configuration PHP
- `docker/mysql/my.cnf` pour la configuration MySQL

## Dépannage

### Problèmes courants

1. **Port déjà utilisé**
   ```bash
   # Vérifier les ports utilisés
   lsof -i :8000
   lsof -i :3307
   
   # Modifier les ports dans docker-compose.yml si nécessaire
   ```

2. **Permissions sur les fichiers**
   ```bash
   # Corriger les permissions
   sudo chown -R $USER:$USER .
   chmod -R 755 storage bootstrap/cache
   ```

3. **Cache corrompu**
   ```bash
   # Nettoyer tous les caches
   docker-compose exec app php artisan cache:clear
   docker-compose exec app php artisan config:clear
   docker-compose exec app php artisan view:clear
   docker-compose exec app php artisan route:clear
   ```

4. **Base de données non accessible**
   ```bash
   # Vérifier le statut des conteneurs
   docker-compose ps
   
   # Redémarrer la base de données
   docker-compose restart db
   ```

### Logs et débogage

```bash
# Voir tous les logs
docker-compose logs

# Logs de l'application Laravel
docker-compose logs app

# Logs de la base de données
docker-compose logs db

# Logs du serveur web
docker-compose logs webserver
```

## Développement

### Ajout de nouvelles fonctionnalités

1. Créer une migration :
   ```bash
   docker-compose exec app php artisan make:migration create_nouvelle_table
   ```

2. Créer un modèle :
   ```bash
   docker-compose exec app php artisan make:model NouveauModele
   ```

3. Créer un contrôleur :
   ```bash
   docker-compose exec app php artisan make:controller NouveauControleur
   ```

### Tests

```bash
# Exécuter tous les tests
docker-compose exec app php artisan test

# Exécuter les tests avec couverture
docker-compose exec app php artisan test --coverage
```

## Support

Si vous rencontrez des problèmes :

1. Vérifiez que Docker et Docker Compose sont correctement installés
2. Consultez les logs avec `docker-compose logs`
3. Vérifiez que les ports 8000 et 3307 ne sont pas utilisés par d'autres applications
4. Assurez-vous d'avoir suffisamment d'espace disque et de mémoire

## Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

---

**Bon développement !**
