#!/bin/bash

echo "Installation de l'environnement Laravel Docker"
echo "=================================================="

# Vérification des prérequis
echo "1. Vérification des prérequis..."
if ! command -v docker &> /dev/null; then
    echo "ERREUR: Docker n'est pas installé"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "ERREUR: Docker Compose n'est pas installé"
    exit 1
fi

echo "OK: Docker et Docker Compose sont installés"

# Configuration de l'environnement
echo "2. Configuration de l'environnement..."
if [ ! -f .env ]; then
    cp env.docker.example .env
    echo "OK: Fichier .env créé"
else
    echo "ATTENTION: Le fichier .env existe déjà"
fi

# Construction des images
echo "3. Construction des images Docker..."
docker-compose build --no-cache

# Démarrage des services
echo "4. Démarrage des services..."
docker-compose up -d

# Attendre que les services soient prêts
echo "5. Attente du démarrage des services..."
sleep 10

# Installation des dépendances
echo "6. Installation des dépendances Composer..."
docker-compose exec app composer install --no-interaction

# Génération de la clé
echo "7. Génération de la clé d'application..."
docker-compose exec app php artisan key:generate --no-interaction

# Migrations
echo "8. Exécution des migrations..."
docker-compose exec app php artisan migrate --no-interaction

# Nettoyage du cache
echo "9. Nettoyage du cache..."
docker-compose exec app php artisan cache:clear --no-interaction
docker-compose exec app php artisan config:clear --no-interaction

# Vérification finale
echo "10. Vérification de l'installation..."
if curl -s http://localhost:8000 > /dev/null; then
    echo "OK: Application accessible sur http://localhost:8000"
else
    echo "ERREUR: Problème d'accès à l'application"
fi

echo ""
echo "Installation terminée !"
echo "Application Laravel : http://localhost:8000"
echo "phpMyAdmin : http://localhost:8080"
echo ""
echo "Commandes utiles :"
echo "- Arrêter : docker-compose down"
echo "- Redémarrer : docker-compose restart"
echo "- Logs : docker-compose logs -f"
echo "- Entrer dans le conteneur : docker-compose exec app bash"