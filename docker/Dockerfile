FROM php:8.0-apache

# Install composer
COPY --from=composer:2.0 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
#    build-essential \
#    libpng-dev \
#    libjpeg62-turbo-dev \
#    libfreetype6-dev \
#    locales \
    libyaml-dev \
    libzip-dev \
    zip \
    unzip \
#    jpegoptim optipng pngquant gifsicle \
    curl

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql zip pcntl
#RUN docker-php-ext-configure gd --with-gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/
#RUN docker-php-ext-install gd
RUN pecl install -o -f redis \
    && pecl install -o -f yaml \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis yaml

# Add user for laravel application
RUN groupadd -g 1000 www \
    && useradd -u 1000 -ms /bin/bash -g www www

# Cleanup web root
RUN rm -rf /var/www/* && chown -R 1000:1000 /var/www

RUN a2enmod rewrite

# Configure apache webserver
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# Change apache port to 8080, because non-root user cannot claim port 80
RUN sed -s -i -e "s/80/8080/" /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf

# Install startup scripts globally
COPY docker/start.sh /usr/local/bin/start
RUN chmod a+x /usr/local/bin/start

# Copy existing application directory permissions
COPY --chown=www:www . /var/www

# Change current user to www
USER www

# Install php dependencies
RUN composer install --no-dev --no-interaction --no-progress --no-cache --verbose

# Generate current documentation
ARG APP_VERSION
ENV APP_VERSION=$APP_VERSION
RUN mkdir -p storage/api-docs && make docs | head -n 4

EXPOSE 8080
ENTRYPOINT ["start", "webserver"]
