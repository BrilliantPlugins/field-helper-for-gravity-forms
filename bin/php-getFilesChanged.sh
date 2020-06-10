#!/bin/bash
# What's happening here?
#
# 1. We get names and statuses of files that differ in current branch from their state in origin/master.
# These come in form (multiline)
# 2. The output from git diff is filtered by unix grep utility, we only need files with names ending in .php
# 3. One more filter: filter *out* (grep -v) all lines starting with R or D.
# D means "deleted", R means "renamed"
# 4. The filtered status-name list is passed on to awk command, which is instructed to take only the 2nd part
# of every line, thus just the filename
git fetch origin;
CI_MERGE_REQUEST_TARGET_BRANCH_NAME="develop";
echo $CI_MERGE_REQUEST_TARGET_BRANCH_NAME;
git diff --name-status origin/$CI_MERGE_REQUEST_TARGET_BRANCH_NAME | grep '\.php$' | grep -v "^[RD]" | awk '{ print $2 }'
