FROM php:7.2-apache

# Update to use Debian archive repositories (Buster is archived)
RUN sed -i 's/deb.debian.org/archive.debian.org/g' /etc/apt/sources.list \
    && sed -i 's|security.debian.org|archive.debian.org|g' /etc/apt/sources.list \
    && sed -i '/stretch-updates/d' /etc/apt/sources.list

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libxrender1 \
    libfontconfig1 \
    libxtst6 \
    wget \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js 10 and npm (from Debian Buster repos — required for gulp 3 compatibility)
RUN apt-get update && apt-get install -y nodejs npm \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    intl \
    gd \
    zip \
    opcache

# Install Xdebug
RUN pecl install xdebug-2.9.8 \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_autostart=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install Composer 1.x (required for Symfony 2.8)
COPY --from=composer:1 /usr/bin/composer /usr/bin/composer

# Install wkhtmltopdf for PDF generation
RUN apt-get update && apt-get install -y wkhtmltopdf \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite

# Configure Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/web
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies (skip scripts to avoid cache:clear error during build)
RUN composer install --no-interaction --no-scripts --optimize-autoloader

# Create directories and set permissions
RUN mkdir -p var/cache var/logs var/sessions web/uploads \
    && chown -R www-data:www-data var web/uploads \
    && chmod -R 775 var web/uploads

# Expose port 80
EXPOSE 80
