# Використовуємо офіційний образ PHP з Apache, оптимізований під Laravel
FROM php:8.2-apache

# Встановлюємо необхідні системні розширення для роботи бази даних PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Увімкнення модуля Apache rewrite для правильної маршрутизації Laravel (routes)
RUN a2enmod rewrite

# Змінюємо кореневу папку Apache на public, як того вимагає Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Копіюємо всі файли нашого проєкту в контейнер
COPY . /var/www/html

# Встановлюємо правильні права доступу для папок кешу та логів
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Відкриваємо порт для Render
EXPOSE 80