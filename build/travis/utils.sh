#!/usr/bin/env bash

nanoseconds() {
    local cmd="date"
    local format="+%s%N"
    local os=$(uname)

    if hash gdate > /dev/null 2>&1; then
        cmd="gdate"
    elif [[ "$os" = Darwin ]]; then
        format="+%s000000000"
    fi

    $cmd -u $format
}

# tfold is a helper to create folded reports
# Arguments:
#   $1 fold name
#   $2 command to execute
tfold () {
    local title=$1
    local fold=$(echo "$title" | sed -r 's/[^-_A-Za-z\d]+/./g')
    shift
    local id=$(printf %08x $(( RANDOM * RANDOM )))
    local start=$(nanoseconds)
    echo -e "travis_fold:start:$fold"
    echo -e "travis_time:start:$id"
    echo -e "\\e[1;34m$title\\e[0m"
    bash -xc "$*" 2>&1
    local ok=$?
    local end=$(nanoseconds)
    echo -e "\\ntravis_time:end:$id:start=$start,finish=$end,duration=$(($end-$start))"
    (exit $ok) &&
        echo -e "\\e[32mOK\\e[0m $title\\n\\ntravis_fold:end:$fold" ||
        echo -e "\\e[41mKO\\e[0m $title\\n"
    (exit $ok)
}

function try () {
    [[ $- = *e* ]]; SAVED_EXCEPTION=$?
    set +e
}

function catch () {
    export ex_code=$?
    (( $SAVED_EXCEPTION )) && set +e
    return $ex_code
}

export -f nanoseconds;
export -f tfold;
export -f try;
export -f catch;
