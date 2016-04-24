#!/usr/bin/env bash

# Create logs dir
mkdir -p build/logs

if [[ "$TRAVIS_PHP_VERSION" != "hhvm" && "$CHECK_CS" = true && "$SEND_COVERAGE" = true ]]; then
    try {
        vendor/bin/phpunit -c phpunit.xml.dist -v --coverage-text="php://stdout" --coverage-xml="build/coverage-xml.xml" --coverage-clover="build/logs/clover.xml";
    } catch {
        exit(1)
    }
else
    try {
        vendor/bin/phpunit -c phpunit.xml.dist -v;
    } catch {
        exit(1)
    }
fi

if [[ "$TRAVIS_PHP_VERSION" != "hhvm" && "$HUMBUG" = true ]]; then vendor/bin/humbug; fi
