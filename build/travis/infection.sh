#!/usr/bin/env bash

source ./build/travis/utils.sh

for f in ./src/Viserio/*/*; do
    if [[ -d "$f" && ! -L "$f" ]]; then
        SLUG="$(basename "$f")";
        TYPE="$(basename "${f%/*}")";

        if [[ "$TYPE" = "Component" ]]; then
            TESTSUITE="Narrowspark $SLUG Component";
        elif [[ "$TYPE" = "Bridge" ]]; then
            TESTSUITE="Narrowspark $SLUG Bridge";
        elif [[ "$TYPE" = "Provider" ]]; then
            TESTSUITE="Narrowspark $SLUG Provider";
        fi

        INFECTION_PHPUNIT="--verbose --testsuite=\"$TESTSUITE\"";

        tfold "$TESTSUITE Infection Test" "infection --threads=4 --filter=\"$f\" --formatter=progress --test-framework-options=\"$INFECTION_PHPUNIT\""
    fi
done
