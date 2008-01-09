#!/bin/bash

#$> ./snaptest.sh <path>
#        runs snaptest/bootstrap.php --mode=directory --path=<path>
#

# Debian / STD Linux
# phppath=/usr/local/bin/php

# OSX / Darwin
phppath=/opt/local/bin/php

# END CONFIG
filepath=`dirname "$0"`
cd $filepath;

$phppath $filepath/snaptest.php --out=text --php=$phppath $1