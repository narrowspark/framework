#!/usr/bin/env bash

# Create logs dir
mkdir -p build/logs

# tfold is a helper to create folded reports
tfold () {
    title=$1
    fold=$(echo $title | sed -r 's/[^-_A-Za-z\d]+/./g')
    shift
    echo -e "travis_fold:start:$fold\\n\\e[1;34m$title\\e[0m"
    "$2" 2>&1 && echo -e "\\e[32mOK\\e[0m $title\\n\\ntravis_fold:end:$fold" || ( echo -e "\\e[41mKO\\e[0m $title\\n" && exit 1 )
}
export -f tfold

if [[ "$PHPUNIT" = true && "$SEND_COVERAGE" = true ]]; then
    ./vendor/bin/phpunit -c ./phpunit.xml.dist --verbose --coverage-clover=coverage.xml;
elif [[ "$PHPUNIT" = true ]]; then
    for f in ./src/Viserio/*/*; do
        if [[ -d "$f" && ! -L "$f" ]]; then
            SLUG="$(basename $f)";
            TYPE="$(basename ${f%/*})";

            if [[ "$TYPE" = "Component" ]]; then
                TESTSUITE="Narrowspark $SLUG Component Component Suite";
            elif [[ "$TYPE" = "Bridge" ]]; then
                TESTSUITE="Narrowspark $SLUG Bridge Test Suite";
            fi

            tfold "$TESTSUITE" "$("$TEST" -c ./phpunit.xml.dist --testsuite="$TESTSUITE")";
        fi
    done
elif [[ "$PHPSTAN" = true ]]; then
    ./vendor/bin/phpstan analyse -c phpstan.neon -l 5 src/Viserio
fi
