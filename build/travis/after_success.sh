#!/usr/bin/env bash

if [[ "$TRAVIS_PHP_VERSION" != "hhvm" && "$CHECK_CS" = true && "$SEND_COVERAGE" = true && "$HUMBUG" != true ]]; then
    vendor/bin/codacycoverage clover build/logs/coverage.xml
    # Run codecov
    bash <(curl -s https://codecov.io/bash)
fi
