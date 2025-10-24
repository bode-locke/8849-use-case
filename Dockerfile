# ---- Étape 1 : Base PHP ----
FROM php:8.4-fpm

# ---- Installer les extensions nécessaires ----
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd

# ---- Installer Node.js (version 20 LTS) ----
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# ---- Installer Composer ----
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ---- Créer le dossier de travail (cohérent avec docker-compose) ----
WORKDIR /app

# ---- Copier les fichiers de dépendances ----
COPY composer.json composer.lock package.json package-lock.json ./

# ---- Installer les dépendances PHP ----
RUN composer install --no-dev --optimize-autoloader --no-scripts

# ---- Installer les dépendances JS ----
RUN npm install

# ---- Copier tout le projet ----
COPY . .

# ---- Générer la clé Laravel si nécessaire ----
RUN php artisan key:generate --no-interaction || true

# ---- Donner les droits sur le storage et cache Laravel ----
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# ---- Exposer le port de PHP-FPM ----
EXPOSE 9000

CMD ["php-fpm"]
