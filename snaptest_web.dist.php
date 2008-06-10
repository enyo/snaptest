<?php

// snaptest web interface

// STEP 1: Define the absolute path to where snaptest.php is
// Include the ending slash
define('SNAP_WI_PATH', '/path/to/snaptest/directory/');

// STEP 2: Define the absolute path to your top level test directory
// Include the ending slash
define('SNAP_WI_TEST_PATH', '/path/to/my/tests/');

// STEP 3: Define the URL of this file. This way, we can find it
// once more without weird script url hackery
define('SNAP_WI_URL_PATH', 'http://www.example.com/path/to/snaptest_web.dist.php');

// STEP 4: Set a key. You only need this if you are putting your
// tests on a publicly accessible server. This also offers very minimal
// protection. If you're actually going this far in the protecting bit,
// it is probably time to read up on .htaccess and possibly write your own
// web stub to do security.
// A blank string means no key will be used.
define('SNAP_WI_KEY', '');

// STEP 5: set the matching path Files matching this pattern will be testable
define('SNAP_WI_TEST_MATCH', '^.*\.stest\.php$');

// STEP 6: Relax, you're done.
// Go to http://www.example.com/path/to/snaptest_web.dist.php

// --------------------------------------------------------------------------

// include the snaptest web core, which will handle the request, components
// etc. All the heavy lifting should happen well out of sight.
require_once SNAP_WI_PATH . 'snaptest_webcore.php';

