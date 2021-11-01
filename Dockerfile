# Based on https://github.com/ulsmith/alpine-apache-php7

FROM alpine:edge

RUN echo "http://dl-cdn.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories

RUN apk update && apk upgrade && apk add \
	bash \
	apache2 \
	php7-apache2 \
	curl \
	ca-certificates \
	openssl \
	openssh \
	git \
	php7 \
	php7-phar \
	php7-json \
	php7-iconv \
	php7-openssl \
	tzdata \
	openntpd \
	nano

RUN apk add \
	php7-ftp \
	php7-mcrypt \
	php7-mbstring \
	php7-soap \
	php7-gmp \
	php7-pdo_odbc \
	php7-dom \
	php7-pdo \
	php7-zip \
	php7-mysqli \
	php7-sqlite3 \
	php7-pdo_pgsql \
	php7-bcmath \
	php7-gd \
	php7-odbc \
	php7-pdo_mysql \
	php7-pdo_sqlite \
	php7-gettext \
	php7-xml \
	php7-xmlreader \
	php7-xmlwriter \
	php7-tokenizer \
	php7-xmlrpc \
	php7-bz2 \
	php7-pdo_dblib \
	php7-curl \
	php7-ctype \
	php7-session \
	php7-redis \
	php7-exif \
	php7-intl \
	php7-fileinfo \
	php7-ldap \
	php7-apcu

RUN apk add php7-simplexml

RUN cp /usr/bin/php7 /usr/bin/php && rm -f /var/cache/apk/*

RUN sed -i "s/#LoadModule expires_module/LoadModule\ expires_module/" /etc/apache2/httpd.conf \
	&& sed -i "s/#LoadModule\ headers_module/LoadModule\ headers_module/" /etc/apache2/httpd.conf \
	&& sed -i "s/#LoadModule\ rewrite_module/LoadModule\ rewrite_module/" /etc/apache2/httpd.conf \
	&& sed -i "s/#LoadModule\ session_module/LoadModule\ session_module/" /etc/apache2/httpd.conf \
	&& sed -i "s/#LoadModule\ session_cookie_module/LoadModule\ session_cookie_module/" /etc/apache2/httpd.conf \
	&& sed -i "s/#LoadModule\ session_crypto_module/LoadModule\ session_crypto_module/" /etc/apache2/httpd.conf \
	&& sed -i "s/#LoadModule\ deflate_module/LoadModule\ deflate_module/" /etc/apache2/httpd.conf \
	&& sed -i "s#^DocumentRoot \".*#DocumentRoot \"/app/public\"#g" /etc/apache2/httpd.conf \
	&& sed -i "s#/var/www/localhost/htdocs#/app/public#" /etc/apache2/httpd.conf \
	&& printf "\n<Directory \"/app/public\">\n\tAllowOverride All\n</Directory>\n" >> /etc/apache2/httpd.conf

RUN mkdir /app && mkdir /app/public && chown -R apache:apache /app && chmod -R 755 /app && mkdir bootstrap

COPY ss-bookmarks.php /app/public/index.php
RUN chown -R apache:apache /app/public && chmod -R 755 /app/public

COPY docker-entrypoint.sh /bootstrap

RUN chmod +x /bootstrap/docker-entrypoint.sh

ENTRYPOINT []
CMD ["/bootstrap/docker-entrypoint.sh"]