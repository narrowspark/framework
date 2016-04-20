#!/usr/bin/env bash

# Create logs dir
mkdir -p build/logs

if [[ "$TRAVIS_PHP_VERSION" != "hhvm" && "$CHECK_CS" = true && "$SEND_COVERAGE" = true ]]; then
    vendor/bin/phpunit -c phpunit.xml.dist --verbose --coverage-text="php://stdout" --coverage-clover="build/logs/coverage.xml";
else
    vendor/bin/phpunit -c phpunit.xml.dist --verbose;
fi

if [[ "$TRAVIS_PHP_VERSION" != "hhvm" && "$HUMBUG" = true ]]; then vendor/bin/humbug; fi
