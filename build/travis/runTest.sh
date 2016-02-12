#!/usr/bin/env bash

SLUG=$(grep -Pho 'viserio/[A-Za-z-\.]+' $1/composer.json | xargs | awk '{print $1}')
printf "\n\n************ Running tests for $SLUG ************\n\n"

cd $1
composer install --no-interaction --prefer-source --ignore-platform-reqs --quiet

TEST="./vendor/bin/phpunit $2"

printf "Command: $TEST\n\n"
if [ "$TRAVIS_PHP_VERSION" == '7.0' ]
then
    phpdbg -qrr $TEST
else
    sh -c "$TEST"
fi
