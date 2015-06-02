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

split cache        src/Brainwave/Cache:git@github.com:n-brainwave/cache.git            "master 0.10.0"
split config       src/Brainwave/Config:git@github.com:n-brainwave/config.git          "master 0.10.0"
split console      src/Brainwave/Console:git@github.com:n-brainwave/console.git        "master 0.10.0"
split container    src/Brainwave/Container:git@github.com:n-brainwave/container.git    "master 0.10.0"
split contracts    src/Brainwave/Contracts:git@github.com:n-brainwave/contracts.git    "master 0.10.0"
split cookie       src/Brainwave/Cookie:git@github.com:n-brainwave/cookie.git          "master 0.10.0"
split database     src/Brainwave/Database:git@github.com:n-brainwave/database.git      "master 0.10.0"
split encrypter    src/Brainwave/Encrypter:git@github.com:n-brainwave/encrypter.git    "master 0.10.0"
split events       src/Brainwave/Events:git@github.com:n-brainwave/events.git          "master 0.10.0"
split exception    src/Brainwave/Exception:git@github.com:n-brainwave/exception.git    "master 0.10.0"
split filesystem   src/Brainwave/Filesystem:git@github.com:n-brainwave/filesystem.git  "master 0.10.0"
split hashing      src/Brainwave/Hashing:git@github.com:n-brainwave/hashing.git        "master 0.10.0"
split http         src/Brainwave/Http:git@github.com:n-brainwave/http.git              "master 0.10.0"
split log          src/Brainwave/Log:git@github.com:n-brainwave/log.git                "master 0.10.0"
split mail         src/Brainwave/Mail:git@github.com:n-brainwave/mail.git              "master 0.10.0"
split queue        src/Brainwave/Queue:git@github.com:n-brainwave/queue.git            "master 0.10.0"
split routing      src/Brainwave/Routing:git@github.com:n-brainwave/routing.git        "master 0.10.0"
split security     src/Brainwave/Security:git@github.com:n-brainwave/security.git      "master 0.10.0"
split session      src/Brainwave/Session:git@github.com:n-brainwave/session.git        "master 0.10.0"
split support      src/Brainwave/Support:git@github.com:n-brainwave/support.git        "master 0.10.0"
split translator   src/Brainwave/Translator:git@github.com:n-brainwave/translator.git  "master 0.10.0"
split view         src/Brainwave/View:git@github.com:n-brainwave/view.git              "master 0.10.0"
