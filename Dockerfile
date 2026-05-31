# Використовуємо офіційний образ PHP з Apache
FROM php:8.2-apache

# Встановлюємо системні залежності та інструменти (включаючи git та zip для Composer)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    zip \
    && docker-php-ext-install pdo pdo_pgsql

# Встановлюємо Composer прямо всередину контейнера
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Увімкнення модуля Apache rewrite для маршрутизації Laravel
RUN a2enmod rewrite

# Налаштування кореневої папки Apache на public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Копіюємо файли проєкту в контейнер
COPY . /var/www/html

# Встановлюємо робочу директорію
WORKDIR /var/www/html

# ЗАПУСКАЄМО ВСТАНОВЛЕННЯ ПАКЕТІВ LARAVEL (виправляє твою помилку)
RUN composer install --no-dev --optimize-autoloader

# Виставляємо правильні права доступу
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Відкриваємо порт
EXPOSE 80