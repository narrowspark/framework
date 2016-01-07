#!/usr/bin/env bash

# Install libevent
if [ "$ADAPTER" = Libevent ]; then (echo "yes" | pecl install "channel://pecl.php.net/libevent-0.1.0"); fi

# Install ev
if [ "$ADAPTER" = Ev ]; then (echo "yes" | pecl install ev); fi
