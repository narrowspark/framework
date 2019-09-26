#!/usr/bin/env bash
#!/usr/bin/env bash

source ./build/travis/try_catch.sh
source ./build/travis/tfold.sh

echo "$COMPONENTS" | parallel --gnu "tfold {} 'cd {} && $COMPOSER_UP && ./vendor/bin/phpstan analyse -c ./phpstan.neon --memory-limit=-1'" || X=1
