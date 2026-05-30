FROM php:8.4-fpm

# Installer les extensions nécessaires (PostgreSQL, etc.)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Copier tout le code
COPY . /app
WORKDIR /app

# Installer Composer (si pas déjà fait)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --ignore-platform-reqs

# Exposer le port (Railway utilisera $PORT)
EXPOSE 8000

# Lancer les migrations puis le serveur
CMD ["sh", "-c", "php bin/console doctrine:migrations:migrate --no-interaction && php -S 0.0.0.0:$PORT -t public"]