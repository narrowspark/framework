#!/usr/bin/env bash

# Create logs dir
mkdir -p build/logs

vendor/bin/phpunit -c phpunit.xml.dist --verbose

if [[ "$TRAVIS_PHP_VERSION" != "hhvm" && "$CHECK_CS" != true && "$SEND_COVERAGE" != true ]]; then vendor/bin/humbug; fi
