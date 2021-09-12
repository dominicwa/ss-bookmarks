FROM php:7.4.6-apache
RUN apt-get update
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY ss-bookmarks.php /var/www/html/index.php
RUN chown www-data:www-data -R /var/www/html