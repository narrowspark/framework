#!/usr/bin/env bash

# Disable xdebug when hhvm or when SEND_COVERAGE is false
if [[ "$SEND_COVERAGE" = false ]]; then
  phpenv config-rm xdebug.ini;
fi

echo "extension = memcached.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
echo "extension = redis.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
echo date.timezone = Europe/Paris >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

# Install mongodb
pecl install -f mongodb-1.1.2

composer global require hirak/prestissimo # Now composer can install components parallel

if [[ "$HUMBUG" = true ]]; then
    composer require humbug/humbug:1.0.0-alpha2;
fi
