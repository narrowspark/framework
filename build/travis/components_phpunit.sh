#!/usr/bin/env bash

source ./build/travis/utils.sh

COMPONENTS=$(find src/Viserio -mindepth 3 -type f -name phpunit.xml.dist -printf '%h\n')

for f in ./src/Viserio/*/*; do
    if [[ -d "$f" && ! -L "$f" ]]; then
        SLUG="$(basename "$f")";
        TYPE="$(basename "${f%/*}")";

        try
            php ./build/travis/build-packages.php HEAD^ $COMPONENTS

            composer validate "$f/composer.json" --strict
            "$f" | composer install

            tfold "$TESTSUITE" "$TEST --verbose";
        catch || {
            exit 1
        }
    fi
done
