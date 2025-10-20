FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN a2enmod rewrite

# Copiamos el código desde la raíz del proyecto (usa la ruta correcta ahora)
COPY ./src /var/www/html

RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html
CMD ["apache2-foreground"]
