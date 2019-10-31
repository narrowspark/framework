#!/usr/bin/env bash

source ./build/travis/try_catch.sh
source ./build/travis/tfold.sh

tfold 'composer update' $COMPOSER_UP

if [[ "$SETUP" = "high" ]]; then
    [[ $LEGACY ]] && EXCLUDE_GROUP=" --exclude-group $LEGACY" || EXCLUDE_GROUP="";

    echo "$COMPONENTS" | parallel --gnu "tfold {} 'cd {} && $COMPOSER_UP && $PHPUNIT$EXCLUDE_GROUP'" || X=1

    COMPONENTS=$(git diff --name-only src/ | grep composer.json || true)

    if [[ $COMPONENTS && $LEGACY && $TRAVIS_BRANCH != master && $TRAVIS_PULL_REQUEST != false ]]; then
        export FLIP='🙃'

        NARROWSPARK_VERSION=$(echo $NARROWSPARK_VERSION | awk '{print $1 - 1}')

        echo -e "\\n\\e[33;1mChecking out Viserio $NARROWSPARK_VERSION and running tests with patched components as deps\\e[0m"

        export COMPOSER_ROOT_VERSION=$NARROWSPARK_VERSION.x-dev

        git fetch --depth=2 origin $NARROWSPARK_VERSION
        git checkout -m FETCH_HEAD

        COMPONENTS=$(echo "$COMPONENTS" | xargs dirname | xargs -n1 -I{} bash -c "[ -e '{}/phpunit.xml.dist' ] && echo '{}'" | sort)

        [[ ! $COMPONENTS ]] || echo "$COMPONENTS" | parallel --gnu "tfold {} 'cd {} && rm composer.lock vendor/ -Rf && composer validate --strict && $COMPOSER_UP && $PHPUNIT$EXCLUDE_GROUP'" || X=1
    fi

    [[ ! $X ]] || (exit 1)
else
    [[ -e ~/php-ext/composer-lowest.lock.tar ]] && tar -xf ~/php-ext/composer-lowest.lock.tar
    tar -cf ~/php-ext/composer-lowest.lock.tar --files-from /dev/null
    php ./build/travis/rm-invalid-lowest-lock-files.php $COMPONENTS

    echo "$COMPONENTS" | parallel --gnu "tfold {} 'cd {} && ([ -e composer.lock ] && composer validate --strict && ${COMPOSER_UP/update/install} || $COMPOSER_UP --prefer-lowest --prefer-stable) && $PHPUNIT'"
    echo "$COMPONENTS" | xargs -n1 -I{} tar --append -f ~/php-ext/composer-lowest.lock.tar {}/composer.lock
fi
