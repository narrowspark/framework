#!/usr/bin/env bash

source ./build/travis/try_catch.sh
source ./build/travis/tfold.sh

echo "$COMPONENTS" | parallel --gnu "tfold {} 'cd {} && $COMPOSER_UP && composer audit'" || X=1

[[ ! $X ]] || (exit 1)
