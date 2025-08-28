FROM php:8.1-cli-alpine

# Instalar dependencias del sistema
RUN apk add --no-cache \
    postgresql-dev \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de composer primero (para optimizar cache de Docker)
COPY composer.json composer.lock ./

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copiar el resto del código
COPY . .

# Crear directorios necesarios y ajustar permisos
RUN mkdir -p logs && chmod 755 logs \
    && chown -R www-data:www-data /var/www/html

# Verificar que composer install funcionó
RUN ls -la vendor/ && composer dump-autoload --optimize

# Exponer puerto 8000
EXPOSE 8000

# Comando de inicio
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]