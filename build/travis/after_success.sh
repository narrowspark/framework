#!/usr/bin/env bash

# Send coverage to scrutinizer
if [[ "$TRAVIS_PHP_VERSION" != hhvm && "$CHECK_CS" = true && "$SEND_COVERAGE" = true ]]; then wget https://scrutinizer-ci.com/ocular.phar; fi
if [[ "$TRAVIS_PHP_VERSION" != hhvm && "$CHECK_CS" = true && "$SEND_COVERAGE" = true ]]; then php ocular.phar code-coverage:upload --format=php-clover build/logs/coverage.xml; fi

# Run codecov
if [[ "$TRAVIS_PHP_VERSION" != hhvm && "$CHECK_CS" = true && "$SEND_COVERAGE" = true ]]; then bash <(curl -s https://codecov.io/bash); fi
