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

        tfold "$TESTSUITE Infection Test" "infection --threads=4 --min-msi=48 --filter=\"$f\" --formatter=progress"
    fi
done
