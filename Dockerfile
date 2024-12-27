FROM dunglas/frankenphp:1-php8.2-alpine
 
COPY --from=qpod/supervisord:alpine /opt/supervisord/supervisord /usr/bin/supervisord

RUN --mount=type=bind,source=.docker,target=/mnt install-php-extensions pcntl pdo_mysql opcache && \
	curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer && \
	cp -v /mnt/etc/supervisord.conf /etc/supervisord.conf


COPY composer.json composer.lock /app
RUN composer install  --prefer-dist --no-autoloader --no-scripts && \
	composer clear-cache

COPY . /app/

RUN composer dump-autoload --optimize && \
	php artisan optimize && \
	php artisan config:clear
 
CMD ["/usr/bin/supervisord"]
