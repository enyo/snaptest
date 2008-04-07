<?php

if (!defined('SNAPTEST_ROOT')) {
    define('SNAPTEST_ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);
    define('SNAPTEST_CORE', SNAPTEST_ROOT . 'core' . DIRECTORY_SEPARATOR);
    define('SNAPTEST_LOADERS', SNAPTEST_CORE . 'loader' . DIRECTORY_SEPARATOR . 'loaders' . DIRECTORY_SEPARATOR);
    define('SNAPTEST_REPORTERS', SNAPTEST_CORE . 'reporter' . DIRECTORY_SEPARATOR . 'reporters' . DIRECTORY_SEPARATOR);
    
    if (!isset($argv) || !is_array($argv)) {
        define('SNAP_CGI_MODE', true);
    }
    else {
        define ('SNAP_CGI_MODE', false);
    }
    
    define('SNAPTEST_TOKEN_START', '===START===');
    define('SNAPTEST_TOKEN_END', '===END===');
    define('SNAP_MANGLE_STRING', '__D_O_T__');
    define('SNAP_STREAM_ENDING_TOKEN', '===SNAPSTREAM_END===');
    
    define('SNAP_MAX_CHILDREN', 10);

    // include the required libraries
    include_once SNAPTEST_CORE . 'functions.php';
    include_once SNAPTEST_CORE . 'snap' . DIRECTORY_SEPARATOR . 'analyzer.php';
    include_once SNAPTEST_CORE . 'snap' . DIRECTORY_SEPARATOR . 'request.php';
    include_once SNAPTEST_CORE . 'snap' . DIRECTORY_SEPARATOR . 'dispatcher.php';
    include_once SNAPTEST_CORE . 'snap' . DIRECTORY_SEPARATOR . 'testaggregator.php';
    include_once SNAPTEST_CORE . 'snap' . DIRECTORY_SEPARATOR . 'snap.php';
    
    include_once SNAPTEST_CORE . 'exceptions' . DIRECTORY_SEPARATOR . 'exceptions.php';
    include_once SNAPTEST_CORE . 'mock' . DIRECTORY_SEPARATOR . 'mock.php';
    include_once SNAPTEST_CORE . 'expectations' . DIRECTORY_SEPARATOR . 'expectations.php';
    include_once SNAPTEST_CORE . 'reporter' . DIRECTORY_SEPARATOR . 'reporter.php';
    include_once SNAPTEST_CORE . 'loader' . DIRECTORY_SEPARATOR . 'loader.php';
    include_once SNAPTEST_CORE . 'testcase' . DIRECTORY_SEPARATOR . 'testcase.php';
    include_once SNAPTEST_CORE . 'file' . DIRECTORY_SEPARATOR . 'file.php';

}
