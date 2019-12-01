#!/usr/bin/env bash

for f in ./src/Viserio/*/*; do
    if [[ -d "$f" && ! -L "$f" ]]; then
        echo "generating changelog for Viserio $(basename "$f") $(basename "${f%/*}")."

        php ./vendor/bin/changelog-generator generate --config="$f/.changelog" --file="$f/CHANGELOG.md"
    fi
done
