# Використовуємо офіційний образ PHP з Apache
FROM php:8.2-apache

# Встановлюємо системні залежності та інструменти
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    zip \
    curl \
    && docker-php-ext-install pdo pdo_pgsql

# ВСТАНОВЛЮЄМО NODE.JS ТА NPM (необхідно для Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y rigging nodejs

# Встановлюємо Composer прямо всередину контейнера
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Увімкнення модуля Apache rewrite для маршрутизації
RUN a2enmod rewrite

# Налаштування кореневої папки Apache на public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Копіюємо всі файли проєкту в контейнер
COPY . /var/www/html
WORKDIR /var/www/html

# Запускаємо встановлення PHP пакетів
RUN composer install --no-dev --optimize-autoloader

# ЗАПУСКАЄМО СБОРКУ ФРОНТЕНДУ ЧЕРЕЗ VITE (виправляє твою помилку)
RUN npm install && npm run build

# Виставляємо правильні права доступу
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Відкриваємо порт
EXPOSE 80