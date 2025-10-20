# Imagen base oficial de PHP con Apache
FROM php:8.2-apache

# Instala extensiones necesarias (puedes agregar más si las necesitas)
RUN docker-php-ext-install pdo pdo_mysql

# Habilita mod_rewrite para URLs amigables
RUN a2enmod rewrite

# Copia Composer desde la imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Copia el código de tu aplicación al contenedor
# (Se copia desde la raíz del contexto del build: ./src)
COPY ./src /var/www/html

# Corrige permisos (importante para entornos Linux)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configuración recomendada de Apache (permite .htaccess)
RUN echo '<Directory /var/www/html/>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/allow-override.conf \
    && a2enconf allow-override

# Exponer el puerto de Apache
EXPOSE 80

# Comando de inicio
CMD ["apache2-foreground"]
