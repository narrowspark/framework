#!/usr/bin/env bash

if [[ "${REMOVE_XDEBUG}" = true ]]; then
  phpenv config-rm xdebug.ini;
fi

if [[ "${MEMCACHED}" = true ]]; then
    echo "extension = memcached" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
fi

echo "extension = redis" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
echo date.timezone = Europe/Berlin >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
