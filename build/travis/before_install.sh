#!/usr/bin/env bash

echo "extension = memcached" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
echo "extension = redis" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
echo date.timezone = Europe/Paris >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

# Install mongodb
pecl install -f mongodb-1.1.2

# Install libsodium
sudo apt-get install make build-essential automake
git clone git://github.com/jedisct1/libsodium.git
cd libsodium
git checkout 1.0.16
./autogen.sh
./configure && make check
sudo make install
cd ..

pecl install -f libsodium
