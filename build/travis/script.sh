#!/usr/bin/env bash

# Create logs dir
mkdir -p build/logs

set +e
bash -e <<TRY
    if [[ "$PHPSTAN" = true ]]; then
        ./vendor/bin/phpstan analyse -c phpstan.neon -l 5 src/Viserio
    fi

    if [[ "$PHPUNIT" = true && "$SEND_COVERAGE" = true ]]; then
        ./vendor/bin/phpunit -c phpunit.xml.dist --verbose --coverage-clover=coverage.xml;
    elif [[ "$PHPUNIT" = true ]]; then
        for f in ./; do
                    print "$f";

        done
    fi
TRY
if [ $? -ne 0 ]; then
  exit 1
fi
