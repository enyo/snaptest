<?php

/**
 * Snaptest Bootstrap File
 **/
include_once 'snap.php';

if ((!isset($argv) && !is_array($argv)) || (!isset($argv[1]))) {
    echo "\nUSAGE: php test.php <path>\n";
    exit;
}

array_shift($argv);

foreach ($argv as $arg) {
    if (strpos($arg, '--') === FALSE) {
        // testable file
        if (!file_exists($arg)) {
            echo "\nFile ".$arg." does not exist.\n";
            exit;
        }
        include $arg;
    }
}


