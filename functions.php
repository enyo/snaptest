<?php

function SNAP_get_long_options($argv) {
    $sequentials = array();
    $arguments = array();

    if (!is_array($argv)) {
        return array();
    }

    foreach ($argv as $idx => $arg) {
        if ($idx == 0) {
            continue;
        }
        
        if (strpos($arg, '--') === FALSE) {
            $sequentials[] = $arg;
            continue;
        }
        
        list($opt_name, $opt_value) = explode('=', substr($arg, 2), 2);

        $arguments[$opt_name] = trim($opt_value, '"\'');
    }
    
    foreach ($sequentials as $idx => $arg) {
        $arguments[$idx] = $arg;
    }
    
    return $arguments;
}