#!/bin/bash

source ./build/travis/try_catch.sh
source ./build/travis/tfold.sh

git subsplit init git@github.com:narrowspark/framework.git

component_array=(
    'src/Viserio/Bridge/Monolog:git@github.com:viserio/monolog-bridge.git'
    'src/Viserio/Bridge/Twig:git@github.com:viserio/twig-bridge.git'
    'src/Viserio/Component/Bus:git@github.com:viserio/bus.git'
    'src/Viserio/Component/Cache:git@github.com:viserio/cache.git'
    'src/Viserio/Component/Config:git@github.com:viserio/config.git'
    'src/Viserio/Component/Console:git@github.com:viserio/console.git'
    'src/Viserio/Component/Container:git@github.com:viserio/container.git'
    'src/Viserio/Component/Contract:git@github.com:viserio/contract.git'
    'src/Viserio/Component/Cookie:git@github.com:viserio/cookie.git'
    'src/Viserio/Component/Cron:git@github.com:viserio/cron.git'
    'src/Viserio/Component/Events:git@github.com:viserio/events.git'
    'src/Viserio/Component/Exception:git@github.com:viserio/exception.git'
    'src/Viserio/Component/Filesystem:git@github.com:viserio/filesystem.git'
    'src/Viserio/Component/Foundation:git@github.com:viserio/foundation.git'
    'src/Viserio/Component/Http:git@github.com:viserio/http.git'
    'src/Viserio/Component/HttpFactory:git@github.com:viserio/http-factory.git'
    'src/Viserio/Component/Log:git@github.com:viserio/log.git'
    'src/Viserio/Component/Mail:git@github.com:viserio/mail.git'
    'src/Viserio/Component/OptionsResolver:git@github.com:viserio/options-resolver.git'
    'src/Viserio/Component/Pagination:git@github.com:viserio/pagination.git'
    'src/Viserio/Component/Parser:git@github.com:viserio/parser.git'
    'src/Viserio/Component/Pipeline:git@github.com:viserio/pipeline.git'
    'src/Viserio/Component/Profiler:git@github.com:viserio/profiler.git'
    'src/Viserio/Component/Queue:git@github.com:viserio/queue.git'
    'src/Viserio/Component/Routing:git@github.com:viserio/routing.git'
    'src/Viserio/Component/Session:git@github.com:viserio/session.git'
    'src/Viserio/Component/StaticalProxy:git@github.com:viserio/statical-proxy.git'
    'src/Viserio/Component/Support:git@github.com:viserio/support.git'
    'src/Viserio/Component/Translation:git@github.com:viserio/translation.git'
    'src/Viserio/Component/Validation:git@github.com:viserio/validation.git'
    'src/Viserio/Component/View:git@github.com:viserio/view.git'
    'src/Viserio/Provider/Twig:git@github.com:viserio/twig-provider.git'
)

for i in "${component_array[@]}"
do
    try
        if [[ "$TRAVIS_TAG" != "false" ]]; then
            OPTION="--tags=\"${TRAVIS_TAG}\"";
        else
            OPTION="--heads=\"master\" --no-tags";
        fi

        tfold ${i##*:} "git subsplit publish $i --update ${OPTION}";
    catch || {
        exit 1
    }
done

rm -rf .subsplit
