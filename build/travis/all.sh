#!/usr/bin/env bash

# Disable xdebug when hhvm or when SEND_COVERAGE is false
if [[ "$DISABLE_XDEBUG" = true ]]; then
  phpenv config-rm xdebug.ini;
fi

echo "extension = memcached.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

# Install mongodb
pecl -q install mongodb

echo "extension=mongodb.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
