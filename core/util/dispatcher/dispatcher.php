<?php

/**
 * Handles multiple processes for both speed and isolation in testing
 * The Snap Dispatch class provides the structure to run each PHP call in
 * its own process. The results of this process are then scraped off of the
 * process' STDIN and sent back to the appropriate callback on complete.
 * When all threads have completed, a global onComplete callback is
 * called.
 **/
class Snap_Dispatcher {
    
    /**
     * Constructor, defines PHP's location and the SnapTest path
     * @param $php the path to php
     * @param $snaptest_path the path to snaptest.php
     **/
    public function __construct($php, $snaptest_path) {
        $this->php = $php;
        $this->snaptest_path = $snaptest_path;
    }
    
    /**
     * Returns the SnapTest.php path
     * @return string
     **/
    protected function getSnaptestPath() {
        return $this->snaptest_path;
    }
    
    /**
     * Returns the path to PHP
     * Under some OSes, additional prefixes may be needed for running
     * PHP properly in the background. If $use_prefix is TRUE, then
     * those prefixes will be prepended to the PHP path.
     * @param $use_prefix should any OS prefixes be used, including calls to "nice"
     * @return string
     **/
    protected function getPHP($use_prefix = TRUE) {
        if (!$use_prefix) {
            return $this->php;
        }
        
        // windows gets a nice via /low
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            return 'start /low /b ' . $this->php;
        }

        $php = $this->php;
        
        // cgi mode needs "quiet" flag
        if (SNAP_CGI_MODE) {
            $php .= ' -q';
        }
        
        // get snap NICE option if set and use it
        $options = Snap_Request::getLongOptions(array('nice' => ''));
        if ($options['nice']) {
            $php = $options['nice'] . ' -n 15 ' . $php;
        }

        return $php;
    }
    
    /**
     * Returns the PHP command suffix
     * On some OSes, additional suffixes may be needed for running PHP
     * in the background or with quiet input.
     * @return string
     **/
    protected function getPHPCommandSuffix() {
        if (substr(PHP_OS, 0, 3) != 'WIN') {
            if (SNAP_CGI_MODE) {
                return ' &';
            }
            else {
                return ' 2>&1 &';
            }
        }
        return '';
    }
    
    /**
     * Returns the Maximum number of Children to run at once
     * @return int
     **/
    protected function getMaxChildren() {
        return SNAP_MAX_CHILDREN;
    }
    
    /**
     * Adapter function for making long option strings
     * @param $options an array of options
     * @return string
     **/
    protected function makeLongOptions($options) {
        return Snap_Request::makeLongOptions($options);
    }
    
    /**
     * Closes an open resource handle from popen
     * @param $handle a resource handle
     * @return boolean
     **/
    protected function closeHandle($handle) {
        return pclose($handle);
    }
    
    /**
     * Opens a resource handle as a background process
     * This opens the PHP process, and returns the execution handle
     * back to the calling function. This is the guts of the fork()
     * @param $call an array of options
     * @return resource of type handle
     **/
    protected function createHandle($call) {
        // add our php path to the $call
        $call['php'] = $this->getPHP(FALSE);
    
        $options = $this->makeLongOptions($call);
    
        $exec = $this->getPHP() . ' ' . $this->getSnaptestPath() . ' ' . $options . $this->getPHPCommandSuffix();

        $exec_handle = popen($exec, "r");

        return $exec_handle;
    }

    /**
     * Dispatch a call for the specified options
     * In order for dispatch to work, a collection of options need
     * to be specified. These options build out the dispatch call, which is
     * then farmed out to the max children allowed in constants.php
     * @param $options the options for the dispatch call
     * @return mixed
     **/
    public function dispatch($options) {
        $key_list = $options['keys'];

        $socket_list = array_fill(0, $this->getMaxChildren(), FALSE);
        $threads_processing = FALSE;
        while (count($key_list) || $threads_processing) {
            // there are files to process
            // look for an empty socket
            for ($i = 0; $i < $this->getMaxChildren(); $i++) {
                if ($socket_list[$i]) {
                    // established socket, read in data, add to stream
                    if (!feof($socket_list[$i]['handle'])) {
                        $data = fread($socket_list[$i]['handle'], 1024);
                    }
                    else {
                        $data = NULL;
                    }

                    if ($data) {
                        // add the data to the stream
                        $socket_list[$i]['stream'] .= $data;
                        
                        // is ending token found?
                        $end = strpos($socket_list[$i]['stream'], SNAP_STREAM_ENDING_TOKEN);
                        if ($end !== FALSE) {
                            // if so, capture output up to ending token, trim
                            $output = trim(substr($socket_list[$i]['stream'], 0, $end));
                            
                            // call complete with the key and the payload
                            call_user_func_array($options['onThreadComplete'], array($socket_list[$i]['key'], $output));
                            
                            // clear the socket
                            $this->closeHandle($socket_list[$i]['handle']);
                            $socket_list[$i] = FALSE;
                        }
                        else {
                            // we're not done yet... continue to next $i
                            continue;
                        }
                    }
                    else {
                        // broken stream, requeue that file
                        call_user_func_array($options['onThreadFail'], array($socket_list[$i]['key'], $socket_list[$i]['stream']));
                        
                        // array_push($key_list, $socket_list[$i]['key']);
                        $this->closeHandle($socket_list[$i]['handle']);
                        $socket_list[$i] = FALSE;
                    }
                }
        
                // if we are out of files to assign, just continue
                // to finish cleaning on this pass
                if (!count($key_list)) {
                    continue;
                }
        
                // available slot, assign it a file
                $key = array_pop($key_list);

                // dispatch an analyze call
                $dispatch = array();
                foreach ($options['dispatch'] as $k => $v) {
                    if ($k == '$key') {
                        $k = $key;
                    }
                    if ($v == '$key') {
                        $v = $key;
                    }
                    $dispatch[$k] = $v;
                }
                $handle = $this->createHandle($dispatch);

                if (!$handle) {
                    // failed opening sub process
                    // put file back on stack, continue
                    array_push($key_list, $key);
                    continue;
                }
        
                // valid handle, assign to socket list
                $socket_list[$i] = array(
                    'key'       => $key,
                    'handle'    => $handle,
                    'stream'    => '',
                );
            }
    
            // check if any threads are processing, and set appropriately
            for ($i = 0; $i < $this->getMaxChildren(); $i++) {
                if ($socket_list[$i]) {
                    $threads_processing = TRUE;
                    break;
                }
                else {
                    $threads_processing = FALSE;
                }
            }
        }
        
        return call_user_func_array($options['onComplete'], array());
    }
}

