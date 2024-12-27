FROM dunglas/frankenphp:1-php8.2-alpine
 
RUN install-php-extensions pcntl pdo_mysql opcache && \
	curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer

COPY composer.json composer.lock /app
RUN composer install  --prefer-dist --no-autoloader --no-scripts && \
	composer clear-cache

COPY . /app/

RUN composer dump-autoload --optimize && \
	php artisan optimize && \
	php artisan config:clear
 
CMD ["php", "artisan", "octane:frankenphp"]
