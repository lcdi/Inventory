#!/bin/bash

user=$1

if [[ -z "$user" ]]; then
    echo "Invalid user"
    exit
fi

git add -A

read -p "Enter the commit message: " message

commit="git commit""$user"" -m \"""$message\""
push="git push""$user"

eval "$commit"
eval "$push"
