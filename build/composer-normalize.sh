#!/usr/bin/env bash

for f in ./src/Viserio/*/*; do
    if [[ -d "$f" && ! -L "$f" ]]; then
        if [[ -f "${f}/composer.json" ]]; then
            echo "Normalizing composer.json in Viserio $(basename "$f") $(basename "${f%/*}")."

            composer normalize "${f}/composer.json"
        fi
    fi
done
