# syntax=docker/dockerfile:1
FROM php:8.4-fpm

# 1. Mettre à jour et installer les dépendances système (dont git)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    zip \
    unzip \
    && docker-php-ext-install pdo_pgsql

# 2. Installer Composer (version officielle et sécurisée)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 3. Copier le code source de l'application
WORKDIR /app
COPY . .

# 4. Installer les dépendances PHP avec une approche plus robuste
#    On définit COMPOSER_ALLOW_SUPERUSER pour éviter les avertissements
#    On retire --no-scripts pour permettre l'exécution des scripts de post-installation
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --ignore-platform-reqs

# 5. Exposer le port que Railway utilisera
EXPOSE 8000

# 6. Lancer les migrations et ensuite le serveur PHP
CMD ["sh", "-c", "php bin/console doctrine:migrations:migrate --no-interaction && php -S 0.0.0.0:$PORT -t public"]