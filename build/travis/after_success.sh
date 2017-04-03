#!/usr/bin/env bash

if [["$SEND_COVERAGE" = true ]]; then
    bash <(curl -s https://codecov.io/bash) -t 1e319602-740e-4a6f-b25f-f4b52f62760d
fi
