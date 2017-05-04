#!/usr/bin/env bash

# Disable xdebug when hhvm or when SEND_COVERAGE is false
if [[ "$SEND_COVERAGE" = false ]]; then
  phpenv config-rm xdebug.ini;
fi

echo "extension = memcached.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
echo "extension = redis.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
echo date.timezone = Europe/Paris >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

# Install mongodb
# pecl install -f mongodb-1.1.2

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
