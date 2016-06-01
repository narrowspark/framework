#!/usr/bin/env bash

# Create logs dir
mkdir -p build/logs
#!/bin/bash

set +e
bash -e <<TRY
    if [[ "$CHECK_CS" = true && "$SEND_COVERAGE" = true ]]; then
        vendor/bin/phpunit -c phpunit.xml.dist -v --coverage-text="php://stdout" --coverage-xml="build/coverage-xml.xml" --coverage-clover="build/logs/clover.xml";
    else
        vendor/bin/phpunit -c phpunit.xml.dist -v;
    fi

    if [[ "$HUMBUG" = true ]]; then vendor/bin/humbug; fi
TRY
if [ $? -ne 0 ]; then
  exit 1
fi
