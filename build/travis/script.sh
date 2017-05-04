#!/usr/bin/env bash

# Create logs dir
mkdir -p build/logs

# tfold is a helper to create folded reports
tfold () {
    title=$1
    fold=$(echo $title | sed -r 's/[^-_A-Za-z\d]+/./g')
    shift
    echo -e "travis_fold:start:$fold\\n\\e[1;34m$title\\e[0m"
    bash -xc "$*" 2>&1 &&
        echo -e "\\e[32mOK\\e[0m $title\\n\\ntravis_fold:end:$fold" ||
        ( echo -e "\\e[41mKO\\e[0m $title\\n" && exit 1 )
}
export -f tfold

if [[ "$PHPUNIT" = true && "$SEND_COVERAGE" = true ]]; then
    ./vendor/bin/phpunit -c phpunit.xml.dist --verbose --coverage-clover=coverage.xml;
elif [[ "$PHPUNIT" = true ]]; then
    for f in ./src/Viserio/*/*; do
        if [[ -d "$f" && ! -L "$f" ]]; then
            SLUG="$(basename $f)";
            TYPE="$(basename ${f%/*})";

            if [[ "$TYPE" = "Component" ]]; then
                tfold ./vendor/bin/phpunit -c phpunit.xml.dist --testsuite="Narrowspark $SLUG Component Test Suite" --verbose;
            elif [[ "$TYPE" = "Bridge" ]]; then
                tfold ./vendor/bin/phpunit -c phpunit.xml.dist --testsuite="Narrowspark $SLUG Bridge Test Suite" --verbose;
            fi
        fi
    done
elif [[ "$PHPSTAN" = true ]]; then
    ./vendor/bin/phpstan analyse -c phpstan.neon -l 5 src/Viserio
fi
