FROM php:8.4-apache

# Install system dependencies, cron, wget, and Composer
RUN apt-get update && apt-get install -y \
    unzip \
    zip \
    cron \
    wget \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions for PDO and MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copy custom php.ini
COPY php.ini /usr/local/etc/php/

# Update Apache DocumentRoot if needed
RUN sed -i -e 's|/var/www/html|/var/www/html/www|g' /etc/apache2/sites-available/000-default.conf

# Enable mod_rewrite
RUN a2enmod rewrite

# Copy crontab file
COPY my-cron /etc/cron.d/my-cron
RUN chmod 0644 /etc/cron.d/my-cron
RUN crontab /etc/cron.d/my-cron

# Ensure logs folder exists (cron may need)
RUN mkdir -p /var/log/cron

# Start cron and Apache together
CMD ["bash", "-c", "cron && apache2-foreground"]
