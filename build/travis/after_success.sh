#!/usr/bin/env bash

if [[ "$CHECK_CS" = true && "$SEND_COVERAGE" = true]]; then
    # Run codecov
    bash <(curl -s https://codecov.io/bash)
    wget https://scrutinizer-ci.com/ocular.phar
    php ocular.phar code-coverage:upload --access-token="a8a3ec5b2ec383bfeb6c161acd5950fffb54e507a27cf61646e28318285c31c3" --format=php-clover coverage.xml
fi
