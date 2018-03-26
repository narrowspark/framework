#!/usr/bin/env bash

if [[ "$REMOVE_XDEBUG" = true ]]; then
  phpenv config-rm xdebug.ini;
fi

INI = ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

echo date.timezone = Europe/Berlin >> $INI
echo memory_limit = -1 >> $INI
echo session.gc_probability = 0 >> $INI
echo opcache.enable_cli = 1 >> $INI
echo "extension = memcached" >> $INI
echo "extension = redis" >> $INI

# Install mongodb
pecl install -f mongodb-1.1.2

# Install libsodium
sudo apt-get install make build-essential automake
git clone git://github.com/jedisct1/libsodium.git
cd libsodium
git checkout 1.0.13
./autogen.sh
./configure && make check
sudo make install
cd ..

pecl install -f libsodium
