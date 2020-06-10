#!/bin/bash

git fetch origin;
last_status=0
status=0

for f in `git diff --name-status HEAD^ HEAD | grep '\.php$' | grep -v "^[RD]" | awk '{ print $2 }'`;
do
    message=`php -l $f`
    last_status="$?";
    if [ "$last_status" -ne "0" ]; then
        echo $message;
        status="$last_status";
        else echo "Scanned $f";
    fi
done

if [ "$status" -ne "0" ]; then echo "PHP syntax validation failed!"
    exit 1
fi
