#!/usr/bin/env bash

cd $1
SLUG=$(grep -o 'viserio/[A-Za-z\-\.]*' composer.json | xargs | awk '{print $1}')

printf "\n\n\n\n\n\n"
printf "****************************************************************************************\n"
printf "****************************************************************************************\n"
printf "****************************************************************************************\n"
printf "***\n"
printf "***  Opening directory: $1 \n"
printf "***  Running tests for: $SLUG \n"
printf "***\n"
printf "****************************************************************************************\n"
printf "****************************************************************************************\n\n"

composer update --no-interaction || exit -1

if [[ "$CHECK_CS" = true && "$SEND_COVERAGE" = true ]]; then
    sh -c "$TEST" -c phpunit.xml.dist --verbose --coverage-text="php://stdout" --coverage-clover=coverage.xml;
else
    sh -c "$TEST" -c phpunit.xml.dist --verbose || exit -1
fi
