#!/usr/bin/env bash

echo
if [[ -n ${DRONE_COMMIT_REFSPEC} &&  ! ${DRONE_COMMIT_REFSPEC} =~ version\/noid\/.+ ]]; then
    echo "Skip CA bundle check"
    exit 0
fi

echo "Fetching latest ca-bundle.crt ..."
curl -o resources/config/ca-bundle.crt https://curl.haxx.se/ca/cacert.pem

echo
outdated=$(git diff --name-only | grep "resources/config/ca-bundle.crt")
if [ "${outdated}" = "resources/config/ca-bundle.crt" ]; then
    echo "CA bundle is not up to date."
    echo "Please run: bash build/ca-bundle-checker.sh"
    echo "And commit the result"
    exit 1
fi

echo "CA bundle is up to date."
exit 0
