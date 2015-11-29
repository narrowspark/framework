#!/bin/bash

split()
{
    SUBDIR=$1
    SPLIT=$2
    HEADS=$3

    mkdir -p $SUBDIR;

    pushd $SUBDIR;

    for HEAD in $HEADS
    do

        mkdir -p $HEAD

        pushd $HEAD

        git subsplit init git@github.com:laravel/framework.git
        git subsplit update

        time git subsplit publish --heads="$HEAD" --no-tags "$SPLIT"

        popd

    done

    popd
}

split cache        src/Viserio/Cache:git@github.com:n-viserio/cache.git            "master 0.10.0"
split config       src/Viserio/Config:git@github.com:n-viserio/config.git          "master 0.10.0"
split console      src/Viserio/Console:git@github.com:n-viserio/console.git        "master 0.10.0"
split container    src/Viserio/Container:git@github.com:n-viserio/container.git    "master 0.10.0"
split contracts    src/Viserio/Contracts:git@github.com:n-viserio/contracts.git    "master 0.10.0"
split cookie       src/Viserio/Cookie:git@github.com:n-viserio/cookie.git          "master 0.10.0"
split database     src/Viserio/Database:git@github.com:n-viserio/database.git      "master 0.10.0"
split encrypter    src/Viserio/Encrypter:git@github.com:n-viserio/encrypter.git    "master 0.10.0"
split events       src/Viserio/Events:git@github.com:n-viserio/events.git          "master 0.10.0"
split exception    src/Viserio/Exception:git@github.com:n-viserio/exception.git    "master 0.10.0"
split filesystem   src/Viserio/Filesystem:git@github.com:n-viserio/filesystem.git  "master 0.10.0"
split hashing      src/Viserio/Hashing:git@github.com:n-viserio/hashing.git        "master 0.10.0"
split http         src/Viserio/Http:git@github.com:n-viserio/http.git              "master 0.10.0"
split log          src/Viserio/Log:git@github.com:n-viserio/log.git                "master 0.10.0"
split mail         src/Viserio/Mail:git@github.com:n-viserio/mail.git              "master 0.10.0"
split pipeline     src/Viserio/Mail:git@github.com:n-viserio/pipeline.git          "0.10.0"
split queue        src/Viserio/Queue:git@github.com:n-viserio/queue.git            "0.10.0"
split routing      src/Viserio/Routing:git@github.com:n-viserio/routing.git        "master 0.10.0"
split session      src/Viserio/Session:git@github.com:n-viserio/session.git        "master 0.10.0"
split support      src/Viserio/Support:git@github.com:n-viserio/support.git        "master 0.10.0"
split translator   src/Viserio/Translator:git@github.com:n-viserio/translator.git  "master 0.10.0"
split view         src/Viserio/View:git@github.com:n-viserio/view.git              "master 0.10.0"
