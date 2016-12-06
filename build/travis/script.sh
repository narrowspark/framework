#!/usr/bin/env bash

# Create logs dir
mkdir -p build/logs
#!/bin/bash

if [[ "$PHPSTAN" = true ]]; then
    phpstan analyse src/Viserio
fi

set +e
bash -e <<TRY
    if [[ "$CHECK_CS" = true && "$SEND_COVERAGE" = true ]]; then
        vendor/bin/phpunit -c phpunit.xml.dist --verbose --coverage-text="php://stdout" --coverage-clover=coverage.xml;
    else
        vendor/bin/phpunit -c phpunit.xml.dist --verbose;
    fi

    if [[ "$HUMBUG" = true ]]; then vendor/bin/humbug; fi
TRY
if [ $? -ne 0 ]; then
  exit 1
fi
