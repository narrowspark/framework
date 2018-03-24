#!/usr/bin/env bash

source ./build/travis/utils.sh

COMPONENTS=$(find src/Viserio -mindepth 3 -type f -name phpunit.xml.dist -printf '%h\n')

try
    php ./build/travis/build_packages.php HEAD^ $COMPONENTS

    if [[ "$SETUP" = "high" ]]; then
        echo "$COMPONENTS" | parallel --gnu -j10% "tfold {} 'cd {} && $COMPOSER_UP && $TEST --verbose'"
    elif [[ "$SETUP" = "lowest" ]]; then
        echo "$COMPONENTS" | parallel --gnu -j10% "tfold {} 'cd {} && $COMPOSER_UP --prefer-lowest --prefer-stable && $TEST --verbose'"
    fi
catch || {
    exit 1
}
