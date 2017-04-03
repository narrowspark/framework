#!/usr/bin/env bash

# Create logs dir
mkdir -p build/logs
#!/bin/bash

set +e
bash -e <<TRY
    if [[ "$PHPSTAN" = true ]]; then
        ./vendor/bin/phpstan analyse -c phpstan.neon -l 5 src/Viserio
    fi

    if [[ "$PHPUNIT" = true && "$SEND_COVERAGE" = true ]]; then
        ./vendor/bin/phpunit -c phpunit.xml.dist --verbose --coverage-text="php://stdout" --coverage-clover=coverage.xml;
    elif [[ "$PHPUNIT" = true ]]; then
        ./vendor/bin/phpunit -c phpunit.xml.dist --verbose;
    fi
TRY
if [ $? -ne 0 ]; then
  exit 1
fi
