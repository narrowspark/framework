#!/usr/bin/env bash

source ./build/travis/utils.sh

for f in ./src/Viserio/*/*; do
    if [[ -d "$f" && ! -L "$f" ]]; then
        SLUG="$(basename "$f")";
        TYPE="$(basename "${f%/*}")";

        if [[ "$TYPE" = "Component" ]]; then
            TESTSUITE="Narrowspark $SLUG Component Infection Test";
        elif [[ "$TYPE" = "Bridge" ]]; then
            TESTSUITE="Narrowspark $SLUG Bridge Infection Test";
        elif [[ "$TYPE" = "Provider" ]]; then
            TESTSUITE="Narrowspark $SLUG Provider Infection Test";
        fi

        tfold "$TESTSUITE" "infection --min-msi=48 --min-covered-msi=70 --threads=4 --filter=\"$f\" --formatter=progress --test-framework-options=\"--verbose --testsuite=\"$TESTSUITE\"\""
    fi
done
