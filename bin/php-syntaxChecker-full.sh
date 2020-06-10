#!/bin/bash

php_lint_file()
{
    local php_file="$1"
    php -l "$php_file" &> /dev/null
    if [ "$?" -ne 0 ]
    then
        echo -e "[FAIL] $php_file"
        return 1
    fi
}

export -f php_lint_file

find . -name '*.php' $(printf "! -wholename %s " $(cat bin/php-syntaxChecker-skipFiles)) | parallel -j 8 php_lint_file {}

if [ "$?" -ne 0 ]
then
    exit 1
fi

