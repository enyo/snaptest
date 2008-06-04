<?php

// getfiles module. gets a list of files and returns them

// generate list of files to test
if (is_dir(SNAP_WI_TEST_PATH)) {
    $file_list = SNAP_recurse_directory(SNAP_WI_TEST_PATH, SNAP_WI_TEST_MATCH);
}
else {
    $file_list = array(SNAP_WI_TEST_PATH);
}

echo json_encode($file_list);

exit;
