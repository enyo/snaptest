<?php

if (!defined('SNAPTEST_ROOT')) {
    define('SNAPTEST_ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);
}

include_once SNAPTEST_ROOT.'snap.php';

// ------------------------------------------------------------
// END SETUP
// BEGIN ACTUAL SNAP TEST
// ------------------------------------------------------------
$files = array(
    'exceptions.test.php',
    'expectations.test.php',
    'file.test.php',
    'input/file.test.php',
    'mock.test.php',
    'output/text.test.php',
    'snap.test.php',
    'testcase.test.php',
);

$snap = new Snap_Tester('text');

foreach ($files as $idx => $file) {
    $files[$idx] = dirname(__FILE__).DIRECTORY_SEPARATOR.$file;
}

$snap->addInput('file', $files);
$snap->runTests();

?>
