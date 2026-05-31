# === ЕТАП 1: Збірка фронтенду (Node) ===
FROM node:18-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# === ЕТАП 2: Основний сервер (PHP + Apache) ===
FROM php:8.2-apache

# Встановлюємо системні залежності для PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    zip \
    && docker-php-ext-install pdo pdo_pgsql

# Встановлюємо Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Увімкнення модуля Apache rewrite
RUN a2enmod rewrite

# Налаштування кореневої папки Apache на public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Копіюємо всі файли проєкту в контейнер
COPY . /var/www/html
WORKDIR /var/www/html

# Запускаємо встановлення PHP пакетів Laravel
RUN composer install --no-dev --optimize-autoloader

# МАГІЯ: Копіюємо вже скомпілений фронтенд з першого етапу (виправляє помилку Vite)
COPY --from=frontend-builder /app/public/build /var/www/html/public/build

# Виставляємо права доступу
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80