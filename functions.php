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

function SNAP_recurse_directory($path, $xtn) {
    if (!is_dir($path)) {
        return array($path);
    }
    
    $file_list = array();
    
    $handle = opendir($path);
    while (false !== ($file = readdir($handle))) {
        if (substr($file, 0, 1) == '.') {
            continue;
        }

        $file = $path.'/'.$file;
        
        // recursion on directory
        if (is_dir($file)) {
            $file_list = array_merge($file_list, SNAP_recurse_directory($file, $xtn));
            continue;
        }
        
        // is a file, check xtn
        if (!preg_match('#'.$xtn.'#', $file)) {
            continue;
        }
        
        // valid, add
        $file_list[] = $file;
    }
    
    return $file_list;
}