<?php

function SNAP_usage() {
    echo "\n";
    echo "Usage: snaptest.sh [--out=outmode] [--php=phppath] [--help] <path>\n";
    echo "Usage: php snaptest.php [--out=outmode] [--php=phppath] [--help] <path>\n";
    echo "\n";
    echo "<path> :: The path to the test you want to run. Should be a file\n";
    echo "ending in .php or a directory.\n";
    echo "\n";
    echo "--out=outmode :: sets the output handler to 'outmode'. The\n";
    echo "output mode must be located in <snaptest>/outmode.php.\n";
    echo "\n";
    echo "--php=phppath :: set the php path for recursion. If not specified,\n";
    echo "the call 'php' will be used, using whatever is in the current env\n";
    echo "variable.\n";
    echo "\n";
    echo "--match=regex :: Specifies a PCRE regular expression to match. Files\n";
    echo "that match this regular expression will be included by the test\n";
    echo "harness.\n";
    exit;
}

// requires PHP 5.2+
if (version_compare(phpversion(), '5.0.0') < 0) {
    echo "\n";
    echo "SnapTest requires a PHP version >= 5.0.0\n";
    exit;
}

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'constants.php';

define('SNAP_MANGLE_STRING', '__D_O_T__');
if (!isset($argv) || !is_array($argv)) {
    define('SNAP_CGI_MODE', true);
}
else {
    define ('SNAP_CGI_MODE', false);
}

$options = SNAP_get_long_options();

$out_mode = (isset($options['out']) && $options['out']) ? $options['out'] : 'text';
$php = (isset($options['php']) && $options['php']) ? $options['php'] : 'php';
$ofile = (isset($options['ofile']) && $options['ofile']) ? $options['ofile'] : tempnam('/tmp', 'SNAP');
$xtn = (isset($options['match']) && $options['match']) ? $options['match'] : '^.*\.stest\.php$';
$help = (isset($options['help'])) ? true : false;

$path = (isset($options[0]) && $options[0]) ? $options[0] : '';

// help output if no path is specified
if ($path == '' || $help) {
    SNAP_usage();
}


// if (substr($path, 0, 1) != '/') {
//     $path = dirname(__FILE__).DIRECTORY_SEPARATOR.$path;
// }
// 
// $path = '/'.trim($path, '/');

if (is_dir($path)) {
    $file_list = SNAP_recurse_directory($path, $xtn);

    $snap = new Snap_Tester($out_mode);
    $real_output = $snap->getOutput($out_mode);
    
    $report_list = array();

    foreach($file_list as $file) {
        $options = SNAP_make_long_options(array(
            'out'   => 'phpserializer',
            'php'   => $php,
            1       => $file,
        ));
        
        if (SNAP_CGI_MODE) {
            $exec = $php .' -q ' . __FILE__ . ' ' . $options;
        }
        else {
            $exec = $php . ' ' . __FILE__ . ' ' . $options . ' 2>&1';
        }

        $exec_handle = popen($exec, "r");
        if ($exec_handle === false) {
            continue;
        }
        
        // get the contents of the stream off of STDIN
        $read = stream_get_contents($exec_handle);
        
        // close the resource
        pclose($exec_handle);

        // get the report data, and announce the results for that sub directory
        //$results = unserialize(trim($read));
        $matches = array();
        preg_match('/===START===([\s\S]*)===END===/', $read, $matches);

        $results = (isset($matches[1])) ? unserialize($matches[1]) : false;
        $problem_output = substr($read, 0, strpos($read, '===START==='));
        
        if (!$results) {
            
            if (!$read) {
                $read = 'No error output captured. Please ensure your PHP environment allows output of errors.';
            }
            
            $report_list[] = array(
                'type' => 'fatal',
                'message' => ($problem_output) ? $problem_output : $file . ' had a fatal error: '.$read,
                'skip_details' => true,
            );
            
            $real_output->announceTestFail();
            
            unset($matches);
            unset($read);
            continue;
        }
        else {
            if (strlen($problem_output) > 0) {
                $report_list[] = array(
                    'type' => 'debug',
                    'message' => $problem_output,
                    'file' => $file,
                    'skip_details' => true,
                );
            }
        }
        
        // cleanup that string
        unset($matches);
        unset($read);

        foreach ($results as $report) {
            $report_list[] = $report;
            if ($report['type'] == 'case') {
                continue;
            }
            elseif ($report['type'] == 'pass') {
                $real_output->announceTestPass();
                continue;
            }
            elseif ($report['type'] == 'defect') {
                $real_output->announceTestDefect();
                continue;
            }
            elseif ($report['type'] == 'notimplemented') {
                $real_output->announceTestNotImplemented();
                continue;
            }
            elseif ($report['type'] == 'skip') {
                $real_output->announceTestSkip();
                continue;
            }
            else {
                $real_output->announceTestFail();
            }
        }

        // cleanup that unserialized thing
        unset($results);
    }
    
    // create the final proper output
    $real_output->generateReport($report_list);

}
else {
    // testing a single file
    $snap = new Snap_Tester($out_mode);
    $snap->addInput('file', $path);
    $snap->runTests();
}
