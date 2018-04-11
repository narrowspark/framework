#!/usr/bin/env bash

source ./build/appveyor/try_catch.sh

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

        echo "";
        echo "$TESTSUITE";
        echo "";

        try
            sh vendor/bin/phpunit --verbose -c ./phpunit.xml.dist --testsuite="$TESTSUITE";
        catch || {
            exit 1
        }
    fi
done
