#!/usr/bin/env bash

source utils.sh

if [[ "$PHPUNIT" = true ]]; then
    for f in ./src/Viserio/*/*; do
        if [[ -d "$f" && ! -L "$f" ]]; then
            SLUG="$(basename "$f")";
            TYPE="$(basename "${f%/*}")";

            if [[ "$TYPE" = "Component" ]]; then
                TESTSUITE="Narrowspark $SLUG Component Test Suite";
            elif [[ "$TYPE" = "Bridge" ]]; then
                TESTSUITE="Narrowspark $SLUG Bridge Test Suite";
            elif [[ "$TYPE" = "Provider" ]]; then
                TESTSUITE="Narrowspark $SLUG Provider Test Suite";
            fi

            try
                composer validate "$f/composer.json" --strict

                tfold "$TESTSUITE" "$TEST -c ./phpunit.xml.dist --verbose --testsuite=\"$TESTSUITE\"";
            catch || {
                exit 1
            }
        fi
    done
fi
