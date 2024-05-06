# # Gunakan PHP sebagai base image
# FROM php:8.2-fpm

# # Install dependensi yang dibutuhkan oleh Laravel
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
#     libonig-dev \
#     libxml2-dev \
#     libzip-dev

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

# # Set working directory
# WORKDIR /var/www

# # Copy composer files
# COPY composer.json composer.lock ./

# # Install dependencies
# RUN composer install --no-scripts --no-autoloader

# # Copy the rest of the application
# COPY . .


# # Change ownership of our applications
# RUN chown -R www-data:www-data /var/www

# # Expose port 9000 and start php-fpm server
# EXPOSE 9000
# CMD ["php-fpm"]

# Gunakan PHP sebagai base image dengan modul Apache
FROM php:8.2-apache

# Install dependensi yang dibutuhkan oleh Laravel dan Apache
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    nano \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    openssl

# Set lokalisasi
RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    locale-gen

# Set timezone
RUN ln -snf /usr/share/zoneinfo/Asia/Jakarta /etc/localtime && echo Asia/Jakarta > /etc/timezone

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PDO MySQL extension
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Enable Apache modules
RUN a2enmod rewrite ssl

# Set working directory
WORKDIR /var/www

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-scripts --no-autoloader

# Copy the rest of the application
COPY . .

# Change ownership of our applications
RUN chown -R www-data:www-data /var/www

# Copy Apache site configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Generate self-signed SSL certificate
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/server.key -out /etc/ssl/certs/server.crt \
    -subj "/C=ID/ST=Jakarta/L=Jakarta/O=Contoh Company/OU=IT Department/CN=server/emailAddress=kukuhelvin20@gmail.com"

# Expose ports 80 and 443
EXPOSE 80
EXPOSE 443

# Start Apache
CMD ["apache2-foreground"]

