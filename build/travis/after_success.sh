#!/usr/bin/env bash

if [[ "$TRAVIS_PHP_VERSION" != "hhvm" && "$CHECK_CS" = true && "$SEND_COVERAGE" = true && "$HUMBUG" != true ]]; then
    # Send coverage to scrutinizer
    wget https://scrutinizer-ci.com/ocular.phar
    php ocular.phar code-coverage:upload --format=php-clover build/logs/coverage.xml
    # Run codecov
    bash <(curl -s https://codecov.io/bash)
fi
