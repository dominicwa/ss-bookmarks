#!/bin/sh
set -e

# Set/sync the timezone/time.

if [ "$CONTAINER_TIMEZONE" ]; then
	cp /usr/share/zoneinfo/${CONTAINER_TIMEZONE} /etc/localtime && \
	echo "${CONTAINER_TIMEZONE}" >  /etc/timezone && \
	echo "Container timezone set to: $CONTAINER_TIMEZONE"
fi

ntpd

# Set Apache server name

if [ ! -z "$APACHE_SERVER_NAME" ]
	then
		sed -i "s/#ServerName www.example.com:80/ServerName $APACHE_SERVER_NAME/" /etc/apache2/httpd.conf
		echo "Changed server name to '$APACHE_SERVER_NAME'..."
	else
		echo "NOTICE: Change 'ServerName' globally and hide server message by setting environment variable >> 'APACHE_SERVER_NAME=your.server.name' in docker command or docker-compose file"
fi

# PHP Config

if [ ! -z "$PHP_DATE_TIMEZONE" ]; then sed -i "s/\;\?\\s\?date.timezone =.*/date.timezone = $PHP_DATE_TIMEZONE/" /etc/php7/php.ini && echo "Set PHP date.timezone = $PHP_DATE_TIMEZONE..."; fi
if [ ! -z "$PHP_ERROR_REPORTING" ]; then sed -i "s/\;\?\\s\?error_reporting = .*/error_reporting = $PHP_ERROR_REPORTING/" /etc/php7/php.ini && echo "Set PHP error_reporting = $PHP_ERROR_REPORTING..."; fi
if [ ! -z "$PHP_DISPLAY_ERRORS" ]; then sed -i "s/\;\?\\s\?display_errors = .*/display_errors = $PHP_DISPLAY_ERRORS/" /etc/php7/php.ini && echo "Set PHP display_errors = $PHP_DISPLAY_ERRORS..."; fi
if [ ! -z "$PHP_VARIABLES_ORDER" ]; then sed -i "s/\;\?\\s\?variables_order = .*/variables_order = $PHP_VARIABLES_ORDER/" /etc/php7/php.ini && echo "Set PHP variables_order = $PHP_VARIABLES_ORDER..."; fi

# Start / deploy

echo "Clearing any old processes..."
rm -f /run/apache2/apache2.pid
rm -f /run/apache2/httpd.pid

echo "Starting apache..."
httpd -D FOREGROUND