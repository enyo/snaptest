<?php

require_once 'snap.php';
require_once 'functions.php';

if (isset($argv) && is_array($argv)) {
    $options = SNAP_get_long_options($argv);
}
elseif (isset($_GET) && is_array($_GET)) {
    $options = SNAP_get_long_options($_GET);
}
else {
    echo "\nOptions must be in command line or via GET";
    $options = array();
}

$out_mode = (isset($options['out']) && $options['out']) ? $options['out'] : 'text';
$php = (isset($options['php']) && $options['php']) ? $options['php'] : 'php';

$path = (isset($options[0]) && $options[0]) ? $options[0] : '';

// help output if no path is specified
if ($path == '') {
    echo "\n";
    echo "Usage: snaptest.sh <path>\n";
    echo "Usage: php snaptest.php [--out=outmode] [--php=phppath] <path>\n";
    echo "\n";
    echo "<path> :: The path to the test you want to run. Should be a file\n";
    echo "ending in .test.php or a directory.\n";
    echo "\n";
    echo "--out=outmode :: sets the output handler to 'outmode'. The\n";
    echo "output mode must be located in <snaptest>/outmode.php.\n";
    echo "This is specific to snaptest.php only\n";
    echo "\n";
    echo "--php=phppath :: set the php path for recursion. If not specified,\n";
    echo "the call 'php' will be used, using whatever is in the current env\n";
    echo "variable.\n";
    echo "This is specific to snaptest.php only\n";
    exit;
}

if (substr($path, 0, 1) != '/') {
    $path = dirname(__FILE__).DIRECTORY_SEPARATOR.$path;
}

// start a tester
$snap = new Snap_Tester('textaggregator');

// get an aggregator and a proper output
$aggregator = $snap->getOutput('textaggregator');
$real_output = $snap->getOutput($out_mode);

// create a temporary file
$tmpfile_handle = tmpfile();

// change input mode depending on path
if (is_dir($path)) {
    $handle = opendir($path);
    while (false !== ($file = readdir($handle))) {
        if (substr($file, 0, 1) == '.') {
            continue;
        }

        if (is_dir($file)) {
            // echo 'NOT: '.$path.$file."\n";
            $exec = $php .' '.__FILE__.' --out=textaggregator --php='.$php.' '.$path.$file.' 2>&1';

            $dir_handle = popen($exec, "r");
            
            if ($dir_handle === false) {
                continue;
            }
            
            // get the contents of the stream off of STDIN
            $read = stream_get_contents($dir_handle);
            
            // get the report data, and announce the results for that sub directory
            $results = $aggregator->extractReportData($read);
            
            // report an empty test for every test case
            for ($i = 0; $i < $results['test_cases']; $i++) {
                $real_output->addTestPasses(0, 0, 0, 'aggregate');
            }
            
            // now report the summary
            $real_output->addTestPasses($results['passes'], $results['defects'], $results['tests'], 'aggregate');
            
            // close the resource
            pclose($dir_handle);
            
            // write the contents into the temp file
            fwrite($tmpfile_handle, $read);
            
            // clean up the stream, save memory
            unset($read);
        }
        else {
            if (strtolower(substr($file, -9)) != '.test.php') {
                continue;
            }
            // echo 'IS : '.$path.$file."\n";
            $snap->addInput('file', $path.'/'.$file);
        }
    }
    
}

// capture the output from the file level
ob_start();

// run the tests for any files in top level dir
$snap->runTests();

// capture the output
$output = ob_get_contents();
ob_end_clean();

// report status to the output util
$results = $aggregator->extractReportData($output);

// report an empty test for every test case
for ($i = 0; $i < $results['test_cases']; $i++) {
    $real_output->addTestPasses(0, 0, 0, 'aggregate');
}

// output for the top level files
$real_output->addTestPasses($results['passes'], $results['defects'], $results['tests'], 'aggregate');

// write the output to the file
fwrite($tmpfile_handle, $output);

// seek to start of temp file
fseek($tmpfile_handle, 0);

// read file into local var
$output = stream_get_contents($tmpfile_handle);

// close resource
fclose($tmpfile_handle);

// capture the test windows, convert to an array, build our totals
$results = array(
    'passes' => 0,
    'defects' => 0,
    'test_cases' => 0,
    'tests' => 0,
    'php_errors' => 0,
    'reports' => array(),
);

// extract all tests into a usable array
preg_match_all('/(===BEGIN TEST===\s*.*?\s*===END TEST===)/m', $output, $matches);

// free up memory
unset($matches[0]);

// loop through each test, and add to the proper tally
foreach ($matches[1] as $match) {
    $output = $aggregator->extractReportData($match);

    $results['passes'] += $output['passes'];
    $results['defects'] += $output['defects'];
    $results['test_cases'] += $output['test_cases'];
    $results['tests'] += $output['tests'];
    $results['php_errors'] += $output['php_errors'];
    $results['reports'] = array_merge($results['reports'], $output['reports']);
}

// free more memory
unset($matches);
unset($aggregator);

// create the final proper output
$real_output->createReport($results['reports'],
                           $results['test_cases'],
                           $results['tests'],
                           $results['passes'],
                           $results['defects'],
                           $results['php_errors']);

