#!/usr/bin/env bash
#!/usr/bin/env bash

source ./build/travis/try_catch.sh
source ./build/travis/tfold.sh

for f in ./src/Viserio/*/*; do
    if [[ -d "$f" && ! -L "$f" ]]; then
        SLUG="$(basename "$f")";
        TYPE="$(basename "${f%/*}")";

        if [[ "$TYPE" = "Component" ]]; then
            SUITE="Narrowspark $SLUG Component PHPStan Suite";
        elif [[ "$TYPE" = "Bridge" ]]; then
            SUITE="Narrowspark $SLUG Bridge PHPStan Suite";
        elif [[ "$TYPE" = "Provider" ]]; then
            SUITE="Narrowspark $SLUG Provider PHPStan Suite";
        fi

        try
            tfold "$SUITE" "./vendor/bin/phpstan analyse -c $f/phpstan.neon --memory-limit=-1";
        catch || {
            exit 1
        }
    fi
done
