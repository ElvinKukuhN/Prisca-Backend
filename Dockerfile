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

# Gunakan PHP sebagai base image
FROM php:8.2-fpm

# Install dependensi yang dibutuhkan oleh Laravel
RUN apt-get update && apt-get install -y \
    build-essential \
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
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    apache2 \
    && a2enmod rewrite

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

# Konfigurasi Apache dan virtual host
COPY apache-config.conf /etc/apache2/sites-available/prisca-prisca-backend.conf
RUN a2ensite prisca-prisca-backend.conf
RUN a2dissite 000-default.conf
RUN service apache2 restart

# Expose port 80 and start Apache
EXPOSE 80
CMD ["apache2-foreground"]
