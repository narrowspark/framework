#!/usr/bin/env bash

if [[ "$CHECK_CS" = true && "$SEND_COVERAGE" = true ]]; then
    # Run codecov
    bash <(curl -s https://codecov.io/bash)
fi
