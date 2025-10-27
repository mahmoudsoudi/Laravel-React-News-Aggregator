FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    cron \
    supervisor \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Create log directory
RUN mkdir -p /var/log && chown -R www-data:www-data /var/log

# Copy cron configuration
COPY ../cron/news-aggregator /etc/cron.d/news-aggregator
RUN chmod 0644 /etc/cron.d/news-aggregator \
    && crontab /etc/cron.d/news-aggregator

# Create supervisor configuration for cron
RUN echo '[program:cron]' > /etc/supervisor/conf.d/cron.conf \
    && echo 'command=cron -f' >> /etc/supervisor/conf.d/cron.conf \
    && echo 'autostart=true' >> /etc/supervisor/conf.d/cron.conf \
    && echo 'autorestart=true' >> /etc/supervisor/conf.d/cron.conf \
    && echo 'user=root' >> /etc/supervisor/conf.d/cron.conf \
    && echo 'redirect_stderr=true' >> /etc/supervisor/conf.d/cron.conf \
    && echo 'stdout_logfile=/var/log/cron.log' >> /etc/supervisor/conf.d/cron.conf

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start supervisor to manage both PHP-FPM and cron
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
