
# # Gunakan PHP sebagai base image dengan modul Apache
# FROM php:8.2-apache

# # Install dependensi yang dibutuhkan oleh Laravel dan Apache
# RUN apt-get update && apt-get install -y \
#     build-essential \
#     libpng-dev \
#     libjpeg62-turbo-dev \
#     libfreetype6-dev \
#     locales \
#     zip \
#     jpegoptim optipng pngquant gifsicle \
#     vim \
#     unzip \
#     git \
#     curl \
#     systemctl\
#     nano\
#     libonig-dev \
#     libxml2-dev \
#     libzip-dev \
#     apache2 \
#     openssl \
#     && a2enmod rewrite ssl

# # Set lokalisasi
# RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
#     locale-gen

# # Set timezone
# RUN ln -snf /usr/share/zoneinfo/Asia/Jakarta /etc/localtime && echo Asia/Jakarta > /etc/timezone

# # Install Composer
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# # Clear cache
# RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# # Install PDO MySQL extension
# RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# # Enable Apache modules
# RUN a2enmod rewrite ssl

# RUN mkdir public

# # Set working directory
# WORKDIR /var/www

# # Copy composer files
# COPY composer.json composer.lock ./

# # Install dependencies
# RUN composer install --no-scripts --no-autoloader

# # Copy the rest of the application
# COPY . .

# # Change ownership of our applications
# RUN chmod -R 755 /var/www/public
# RUN chown -R www-data:www-data /var/www/public

# # Konfigurasi Apache dan virtual host
# COPY apache-config.conf /etc/apache2/sites-available/prisca-backend.3mewj5.easypanel.host.conf
# RUN a2ensite prisca-backend.3mewj5.easypanel.host.conf
# RUN a2dissite 000-default.conf

# # Generate self-signed SSL certificate
# RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/server.prisca-backend.3mewj5.easypanel.host.key -out /etc/ssl/certs/server.prisca-backend.3mewj5.easypanel.host.crt \
#     -subj "/C=ID/ST=Jakarta/L=Jakarta/O=Contoh Company/OU=IT Department/CN=server.prisca-prisca-backend.3mewj5.easypanel.host/emailAddress=kukuhelvin20@gmail.com"

# COPY apache-config-ssl.conf /etc/apache2/sites-available/prisca-backend.3mewj5.easypanel.host-ssl.conf
# RUN a2ensite prisca-backend.3mewj5.easypanel.host-ssl.conf

# RUN composer update

# RUN chmod -R 775 /var/www/storage
# RUN chown -R www-data:www-data /var/www/storage

# # Expose ports 80 and 443
# EXPOSE 80
# EXPOSE 443

# # Start Apache
# CMD ["apache2-foreground"]

# Gunakan PHP 8.1 sebagai base image
FROM php:8.1

# Install Nginx dan beberapa dependensi lainnya
RUN apt-get update && apt-get install -y \
    nginx \
    curl \
    git \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    nano \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    openssl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Set lokalisasi
RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    locale-gen

# Set timezone
RUN ln -snf /usr/share/zoneinfo/Asia/Jakarta /etc/localtime && echo Asia/Jakarta > /etc/timezone

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-scripts --no-autoloader

# Copy the rest of the application
COPY . .

# Change ownership of our applications
RUN chmod -R 755 /var/www/public

# Konfigurasi Nginx untuk menangani PHP dengan FastCGI
COPY nginx-config.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/

# Generate self-signed SSL certificate
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/prisca-backend.3mewj5.easypanel.host.key -out /etc/ssl/certs/prisca-backend.3mewj5.easypanel.host.crt \
    -subj "/C=ID/ST=Jakarta/L=Jakarta/O=Contoh Company/OU=IT Department/CN=prisca-prisca-backend.3mewj5.easypanel.host/emailAddress=kukuhelvin20@gmail.com"

# Expose ports 80 and 443
EXPOSE 80
EXPOSE 443

# Start Nginx untuk menangani permintaan langsung ke PHP dengan FastCGI
CMD ["nginx", "-g", "daemon off;"]



