<?php

function SNAP_get_long_options($request) {
    $sequentials = array();
    $arguments = array();
    
    // get our arguments off of $_REQUEST in CGI mode
    if (SNAP_CGI_MODE) {
        foreach ($_REQUEST as $key => $value) {
            if ($value === "") {
                // a value of "" means it had no =, sequential
                $sequentials[] = SNAP_unmangle_request_var($key);
            }
            else {
                // standard key/value pair
                $key = SNAP_unmangle_request_var($key);
                $key = preg_replace('/^--(.*)/', '$1', $key);
                $arguments[$key] = SNAP_unmangle_request_var($value);
            }
        }
    }
    else {
        // CLI mode
        global $argv;
        if (!is_array($argv)) {
            break;
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
    }

    foreach ($sequentials as $idx => $arg) {
        $arguments[$idx] = $arg;
    }
    
    // now, satisfy out output
    foreach ($request as $key => $default) {
        if (isset($arguments[$key]) && $arguments[$key]) {
            $request[$key] = $arguments[$key];
            continue;
        }
        if (isset($arguments[$key]) && is_bool($default)) {
            $request[$key] = true;
            continue;
        }
    }
    
    return $request;
}

function SNAP_make_long_options($opts) {
    $opt_string = '';
    foreach ($opts as $key => $value) {
        if (is_numeric($key)) {
            $opt_string .= ' ' . SNAP_mangle_request_var($value);
        }
        else {
            $opt_string .= ' --' . SNAP_mangle_request_var($key) . '=' . SNAP_mangle_request_var($value);
        }
    }
    
    return $opt_string;
}

function SNAP_unmangle_request_var($var) {
    return str_replace('__D_O_T__', '.', $var);
}
function SNAP_mangle_request_var($var) {
    if (SNAP_CGI_MODE) {
        return str_replace('.', '__D_O_T__', $var);
    }
    else {
        return $var;
    }
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

        if (substr($path, -1) == DIRECTORY_SEPARATOR) {
            $file = $path . $file;
        }
        else {
            $file = $path.DIRECTORY_SEPARATOR.$file;
        }
        
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

