FROM php:8.4-fpm

# Installer les extensions nécessaires (PostgreSQL, zip, etc.)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_pgsql

# Copier tout le code
COPY . /app
WORKDIR /app

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Installer les dépendances Composer (en ignorant les restrictions de plateforme)
RUN composer install --ignore-platform-reqs --no-scripts

# Re-exécuter les scripts Composer après installation
RUN composer run-script post-install-cmd

# Exposer le port (Railway utilisera $PORT)
EXPOSE 8000

# Lancer les migrations puis le serveur
CMD ["sh", "-c", "php bin/console doctrine:migrations:migrate --no-interaction && php -S 0.0.0.0:$PORT -t public"]