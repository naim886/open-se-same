FROM php:8.4-fpm-alpine AS runtime

# Set working directory
WORKDIR /var/www

# Add docker php ext repo
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install system packages needed by the application / runtime.
# Alpine uses apk instead of apt-get. Laravel's required PHP build
# dependencies (libpng, libjpeg, freetype, libicu, libmemcached,
# imagemagick, etc.) are installed automatically by
# install-php-extensions below.
RUN apk add --no-cache \
    curl \
    git \
    unzip \
    zip \
    nginx \
    supervisor \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    && mkdir -p /var/log/nginx /var/log/php \
    && rm -rf /var/cache/apk/*

# Install php extensions
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions \
        bcmath \
        exif \
        gd \
        imagick \
        intl \
        mbstring \
        memcached \
        opcache \
        pcntl \
        pdo_mysql \
        zip

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add user for laravel application (Alpine addgroup/adduser)
RUN addgroup -g 1000 www && \
    adduser -u 1000 -D -G www -s /sbin/nologin www

# Copy code to /var/www
COPY --chown=www:www-data . /var/www

# add root to www group
ADD . /var/www
RUN chown -R www-data:www-data /var/www
RUN chmod -R ug+w /var/www/storage
RUN chmod -Rf 777 /var/www/storage
RUN chmod -Rf 777 /var/www/storage/logs
# RUN chown -R root:root /var/www/public/images

# Copy nginx/php/supervisor configs
RUN cp docker/supervisor.conf /etc/supervisord.conf
RUN cp docker/php.ini /usr/local/etc/php/conf.d/app.ini
# Alpine nginx includes /etc/nginx/http.d/*.conf from its main config.
RUN mkdir -p /etc/nginx/http.d && cp docker/nginx.conf /etc/nginx/http.d/default.conf

COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# PHP Error Log Files
RUN touch /var/log/php/errors.log && chmod 777 /var/log/php/errors.log

# Deployment steps
RUN composer update
RUN php artisan config:cache
RUN php artisan route:cache
#RUN php artisan view:cache
#RUN php artisan optimize
RUN php artisan storage:link

RUN chmod +x /var/www/docker/run.sh
RUN chmod -Rf 777 . /var/www/storage/logs/
#RUN chmod -R 777 /var/www/public/images

# Cache clearing deliberately does NOT run at build time: the default cache
# store is the database, which is not reachable while the image is built, and
# clearing here would also throw away the config/route caches created above.
# docker/run.sh does the clear-and-rebuild at container start instead, where the
# real environment is available.

EXPOSE 80
ENTRYPOINT ["/var/www/docker/run.sh"]
