#!/bin/bash

# change to shell script real location
FPATH=`dirname "$0"`
OPATH=`pwd`
cd $FPATH;

# load options
. getoptx.sh

cd $OPATH

# Auto Locate PHP
PHP=`which php`
if [[ ! -x "$PHP" ]] ; then
    PHP=""
    if [ -z $PHP ] ; then
        if [ -x "/usr/local/bin/php" ] ; then
            PHP="/usr/local/bin/php"
        fi
        if [ -x "/usr/bin/php" ] ; then
            PHP="/usr/bin/php"
        fi
        if [ -x "/opt/local/bin/php" ] ; then
            PHP="/opt/local/bin/php"
        fi
    fi
fi

# choke and die if we couldn't auto-find PHP
if [ -z $PHP ] ; then
    echo "PHP was not found in any common location. You will need to"
    echo "supply the --php=<path> switch."
    exit 0
fi

# parse the options
CMD=""
while getoptex "out. php. match. help;" "$@"
do
    if [ "$OPTOPT" = "php" ] ; then
        if [ -n PHP ] ; then
            echo "OHSHITNOPHPLAWL"
        fi
        PHP=$OPTARG
    fi
    if [ "$OPTOPT" = "help" ] ; then
        CMD="$CMD --help"
    else
        CMD="$CMD --$OPTOPT=$OPTARG"
    fi
done
shift $[OPTIND-1]
for arg in "$@"
do
    CMD="$CMD $arg"
done

# run php on the snaptest.php file with the commands
CMD="$PHP $FPATH/snaptest.php $CMD"
$CMD
