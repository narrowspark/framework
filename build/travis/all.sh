#!/usr/bin/env bash

# Disable xdebug when hhvm or when SEND_COVERAGE is false
if [[ "$DISABLE_XDEBUG" = true ]]; then
  phpenv config-rm xdebug.ini;
fi

if [[ "$PHP" != hhvm* ]]; then
# Doing something with phpenv
phpenv config-add ./build/travis/php/php.ini;
fi
