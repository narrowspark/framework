#!/usr/bin/env bash

# tfold is a helper to create folded reports
# Arguments:
#   $1 fold name
#   $2 command to execute
tfold () {
    title=$1
    fold=$(echo "$title" | sed -r 's/[^-_A-Za-z\d]+/./g')
    shift
    echo -e "travis_fold:start:$fold\\n\\e[1;34m$title\\e[0m"
    bash -xc "$*" 2>&1 && echo -e "\\e[32mOK\\e[0m $title\\n\\ntravis_fold:end:$fold" || ( echo -e "\\e[41mKO\\e[0m $title\\n" && exit 1 )
}

function try () {
    [[ $- = *e* ]]; SAVED_EXCEPTION=$?
    set +e
}

function catch () {
    export ex_code=$?
    (( $SAVED_EXCEPTION )) && set +e
    return $ex_code
}

if [[ "$PHPUNIT" = true && "$SEND_COVERAGE" = true ]]; then
    bash -xc "$TEST -c ./phpunit.xml.dist --verbose --coverage-clover=coverage.xml";
elif [[ "$PHPUNIT" = true ]]; then
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
                tfold "$TESTSUITE" "$TEST -c ./phpunit.xml.dist --verbose --testsuite=\"$TESTSUITE\"";
            catch || {
                exit 1
            }
        fi
    done
elif [[ "$PHPSTAN" = true ]]; then
    ./vendor/bin/phpstan analyse -c phpstan.neon -l 6 src/Viserio
fi
