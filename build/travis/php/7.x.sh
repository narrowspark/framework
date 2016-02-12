#!/usr/bin/env bash

# Install Libuv
if [ "$ADAPTER" = Libuv ]; then (mkdir libuv && (curl -L https://github.com/libuv/libuv/archive/v1.6.1.tar.gz | tar xzf -) && cd libuv-1.6.1 && ./autogen.sh && ./configure --prefix=$(readlink -f `pwd`/../libuv) && make && make install && cd ..); fi

# Install Uv
if [ "$ADAPTER" = Uv ]; then (git clone https://github.com/bwoebi/php-uv && cd php-uv && phpize && ./configure --with-uv=$(readlink -f `pwd`/../libuv) && make install && (echo "extension = uv.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini) && cd ..); fi

# Remove flysystem-azure
composer remove league/flysystem-azure --dev --no-update
