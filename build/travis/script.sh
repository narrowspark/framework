#!/usr/bin/env bash

mkdir -p build/logs
./build/travis/runTests.sh
if [[ "$TRAVIS_PHP_VERSION" != hhvm && "$CHECK_CS" != true && "$SEND_COVERAGE" != true ]]; then vendor/bin/humbug; fi
