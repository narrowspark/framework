#!/usr/bin/env bash

# Create logs dir
mkdir -p build/logs
#!/bin/bash

if [[ "$PHPSTAN" = true ]]; then
    vendor/bin/phpstan analyse src/Viserio
fi

set +e
bash -e <<TRY
    if [[ "$PHPUNIT" = true && "$CHECK_CS" = true && "$SEND_COVERAGE" = true ]]; then
        vendor/bin/phpunit -c phpunit.xml.dist --verbose --coverage-text="php://stdout" --coverage-clover=coverage.xml;
    elif [[ "$PHPUNIT" = true ]]; then
        vendor/bin/phpunit -c phpunit.xml.dist --verbose;
    fi

    if [[ "$HUMBUG" = true ]]; then vendor/bin/humbug; fi
TRY
if [ $? -ne 0 ]; then
  exit 1
fi
