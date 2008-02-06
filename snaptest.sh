#!/bin/bash

# change to shell script real location
FPATH=`dirname "$0"`
OPATH=`pwd`
cd $FPATH;

# load options
. getoptx.sh

cd $OPATH

# Auto Locate PHP
PHPX=`which php`
if [[ ! -x "$PHPX" ]] ; then
    PHPX=""
    if [ -z $PHPX ] ; then
        if [ -x "/usr/local/bin/php" ] ; then
            PHPX="/usr/local/bin/php"
        fi
        if [ -x "/usr/bin/php" ] ; then
            PHPX="/usr/bin/php"
        fi
        if [ -x "/opt/local/bin/php" ] ; then
            PHPX="/opt/local/bin/php"
        fi
    fi
fi

if [[ ! -z PHPX ]] ; then
    PHP=$PHPX
fi

# parse the options
CMD=""
while getoptex "out. php. match. help;" "$@"
do
    if [ "$OPTOPT" = "php" ] ; then
        if [ -x "$OPTARG" ] ; then
            PHP=$OPTARG
        else
            echo "The path of $OPTARG was not a valid php path."
            exit 0
        fi
    fi
    if [ "$OPTOPT" = "help" ] ; then
        CMD="$CMD --help"
    else
        if [ "$OPTOPT" != "php" ] ; then
            CMD="$CMD --$OPTOPT=$OPTARG"
        fi
    fi
done
shift $[OPTIND-1]
PTH=""
for arg in "$@"
do
    PTH="$PTH $arg"
done

if [ -z $PHP ] ; then
    # choke and die if we couldn't auto-find PHP
    echo "PHP was not found in any common location. You will need to"
    echo "supply the --php=<path> switch."
    exit 0
fi

# is the PHP we are using CLI or CGI
CGI=`$PHP -v | grep cgi | wc -l | sed "s/[^0-9]//g"`

# run php on the snaptest.php file with the commands
if [ "$CGI" = "0" ] ; then
    CMD="$PHP -q $FPATH/snaptest.php --php=$PHP $CMD $PTH"
else
    PHPSAFE=`echo "$PHP" | sed "s/\./__D_O_T__/g"`
    CMDSAFE=`echo "$CMD" | sed "s/\./__D_O_T__/g"`
    PTHSAFE=`echo "$PTH" | sed "s/\./__D_O_T__/g"`
    CMD="$PHP -q $FPATH/snaptest.php --php=$PHPSAFE $CMDSAFE $PTHSAFE"
fi
$CMD
