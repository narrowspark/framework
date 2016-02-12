#!/usr/bin/env bash

# Run for each components
COMPONENTS=$(find src/Viserio/ -mindepth 3 -type f -name phpunit.xml.dist -printf '%h\n')

if [ command -v parallel >/dev/null 2>&1 ]
then
    # Exists
    echo $COMPONENTS | parallel --gnu './build/travis/runTest.sh {}'

else
    # Doesn't Exist
    echo $COMPONENTS | xargs -n 1 ./build/travis/runTest.sh
fi

# Fail out if the tests above failed
if [ $? > 0 ]; then exit $?; fi

# Run for main repo. Generate coverage
COVERAGE=coverage.xml
if [[ -f $COVERAGE && "$CHECK_CS" = true && "$SEND_COVERAGE" = true ]]; then rm $COVERAGE; fi

./build/travis/runTest.sh ./ --coverage-clover=$COVERAGE
