#!/usr/bin/env bash

if [[ "$REMOVE_XDEBUG" = true ]]; then
  phpenv config-rm xdebug.ini;
fi

echo "extension = memcached" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
echo "extension = redis" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
echo date.timezone = Europe/Paris >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

# Install libsodium
if [ ! -d "$HOME/.libsodium/lib" ]; then
  git clone -b stable https://github.com/jedisct1/libsodium.git
  cd libsodium
  sudo ./configure --prefix="$HOME/.libsodium"
  sudo make check
  sudo make install
fi
