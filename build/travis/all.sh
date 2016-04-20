#!/usr/bin/env bash

# Disable xdebug when hhvm or when SEND_COVERAGE is false
if [[ "$TRAVIS_PHP_VERSION" != "hhvm" && (("$CHECK_CS" != true && "$SEND_COVERAGE" != true && "$HUMBUG" != true) || ("$CHECK_CS" != true && "$SEND_COVERAGE" != true)) ]]; then
  phpenv config-rm xdebug.ini;
fi

# Doing something with phpenv
if [ "$TRAVIS_PHP_VERSION" != "hhvm" ]; then phpenv config-add ./build/travis/php/php.ini; fi
