#!/usr/bin/env bash

mkdir -p build/logs
vendor/bin/phpunit -c phpunit.xml.dist
if [[ "$TRAVIS_PHP_VERSION" != hhvm && "$CHECK_CS" != true && "$SEND_COVERAGE" != true ]]; then vendor/bin/humbug; fi
