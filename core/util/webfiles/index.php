<?php

// snaptest index file
// creates the layout, includes the necessary components, etc

$urls = array(
    'css'           => Snap_Request::makeURL(array(
        'mode'  => 'resource',
        'file'  => 'css',
        )),
    'js'            => Snap_Request::makeURL(array(
        'mode'  => 'resource',
        'file'  => 'js',
        )),
);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Language" content="en-us" />    
    <title>SnapTest Web Testing Console</title>

    <link rel="stylesheet" type="text/css" href="<?php echo $urls['css'];?>"> 
    <script type="text/javascript" src="<?php echo $urls['js'];?>"></script> 
</head>
<body id="snaptest">
    <div id="test_container"></div>
    <div id="footer_spacer" class="clear"></div>
    <div id="footer_container">
        <div id="footer">
            <h1>SnapTest Web Testing Console</h1>
            <p id="app_status">Loading...</p>
            <div id="app_controls">
                <button id="run_tests" class="run_tests">Run</button>
                <button id="prev_failure" class="review_results">Prev</button>
                <button id="next_failure" class="review_results">Next</button>
            </div>
        </div>
    </div>
</body>
</html>