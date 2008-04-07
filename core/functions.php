<?php

/**
 * Prints out the SNAP Usage Manual (and exposed options)
 * @return string
 **/
function SNAP_usage() {
return <<<SNAPDOC

Usage: snaptest.sh [--out=outmode] [--php=phppath] [--help] <path>
Usage: php snaptest.php [--out=outmode] [--php=phppath] [--help] <path>

<path> :: The path to the test you want to run. Should be a file
ending in .php or a directory.

--out=outmode :: sets the output handler to 'outmode'. The
output mode must be located in <snaptest>/outmode.php.

--php=phppath :: set the php path for recursion. If not specified,
the call 'php' will be used, using whatever is in the current env
variable.

--match=regex :: Specifies a PCRE regular expression to match. Files
that match this regular expression will be included by the test
harness.
SNAPDOC;
}

/**
 * Recursively scans a directory, building an array of files
 * The array of files will match the pattern $xtn. Anything begining
 * with a dot (.) will be skipped
 * @param $path string a starting path, during recursion it's current path
 * @param $xtn string a regular expression to match for files
 * @return array
 **/
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




