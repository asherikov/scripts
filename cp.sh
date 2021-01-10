#!/bin/sh

if test $# -lt 2; 
then
    echo "At least two parameters are required."
    exit 1
fi

eval "TARGET_DIR=\${$#}";
CUR_DIR=`pwd`
PAR_NUM=$#
I=1
while test $I -lt $PAR_NUM;
do
    cd "`dirname "$1"`"
    find "`basename "$1"`" -type f | sort | tar -c -v -T - -f - | tar -x -f - -C "$TARGET_DIR"
    cd "$CUR_DIR"
    shift
    I=`expr $I + 1`
done
