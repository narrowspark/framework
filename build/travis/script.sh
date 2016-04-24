#!/usr/bin/env bash

# Create logs dir
mkdir -p build/logs

if [[ "$TRAVIS_PHP_VERSION" != "hhvm" && "$CHECK_CS" = true && "$SEND_COVERAGE" = true ]]; then
    vendor/bin/phpunit -c phpunit.xml.dist -v --coverage-text="php://stdout" --coverage-xml="build/coverage-xml" --coverage-clover="build/logs/clover.xml";
else
    vendor/bin/phpunit -c phpunit.xml.dist -v;
fi

if [[ "$TRAVIS_PHP_VERSION" != "hhvm" && "$HUMBUG" = true ]]; then vendor/bin/humbug; fi
