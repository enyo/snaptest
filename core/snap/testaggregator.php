<?php

/**
 * Aggregates the results of several tests into a single result
 * This class is used with Snap_Dispatcher to handle requests to test
 * individual files within their environment.
 **/
class Snap_TestAggregator {
    protected $reporter;
    protected $report_list;
    protected $case_list;
    
    /**
     * Constructor- defines an output mode
     * @param $outmode a string of the final output mode
     **/
    public function __construct($outmode) {
        $st = new Snap_Tester($outmode);
        $this->reporter = $st->getOutput($outmode);
        $this->report_list = array();
        $this->case_list = array();
    }
    
    /**
     * Fired when a single test has finished running
     * @param $key a key that describes file/class/test that was running
     * @param $data the data payload for the key
     **/
    public function onThreadComplete($key, $data) {
        
        $key = Snap_Request::decodeTestKey($key);
        $file = $key['file'];
        $class = $key['class'];
        $method = $key['method'];
        
        $matches = array();
        preg_match('/'.SNAPTEST_TOKEN_START.'([\s\S]*)'.SNAPTEST_TOKEN_END.'/', $data, $matches);

        $results = (isset($matches[1])) ? unserialize($matches[1]) : false;
        $problem_output = substr($data, 0, strpos($data, SNAPTEST_TOKEN_START));
        
        if (!$results) {
            if (!$data) {
                $data = 'No error output captured. Please ensure your PHP environment allows output of errors.';
            }
            
            $this->report_list[] = array(
                'type' => 'fatal',
                'message' => ($problem_output) ? $problem_output : $file . ' had a fatal error: '.$data,
                'skip_details' => true,
            );
            
            $this->reporter->announceTestFail();
        }
        else {
            if (strlen($problem_output) > 0) {
                $this->report_list[] = array(
                    'type' => 'debug',
                    'message' => $problem_output,
                    'file' => $file,
                    'skip_details' => true,
                );
            }
        }
        
        foreach ($results as $report) {
            // cases are added only on first occurance
            if ($report['type'] == 'case') {
                if (!isset($this->case_list[$report['class']])) {
                    $this->report_list[] = $report;
                    $this->case_list[$report['class']] = true;
                }
                continue;
            }
            
            // always add this report
            $this->report_list[] = $report;
            if ($report['type'] == 'pass') {
                $this->reporter->announceTestPass();
                continue;
            }
            elseif ($report['type'] == 'defect') {
                $this->reporter->announceTestDefect();
                continue;
            }
            elseif ($report['type'] == 'todo') {
                $this->reporter->announceTestTodo();
                continue;
            }
            elseif ($report['type'] == 'skip') {
                $this->reporter->announceTestSkip();
                continue;
            }
            else {
                $this->reporter->announceTestFail();
            }
        }
    }
    
    /**
     * Fired when the thread did not properly complete
     * For example, this is called when the remote end hangs up, segfaults,
     * or otherwise closes the connection unexpectedly.
     * @param $key a key that describes file/class/test that was running
     * @param $data the data payload for the key
     **/
    public function onThreadFail($key, $data) {
        $key = Snap_Request::decodeTestKey($key);
        $file = $key['file'];
        $class = $key['class'];
        $method = $key['method'];
        
        if (!$data) {
            $data = 'No error output captured. Please ensure your PHP environment allows output of errors.';
        }
        
        $this->report_list[] = array(
            'type' => 'fatal',
            'message' => $file . ' had a fatal error: '.$data,
            'skip_details' => true,
        );
        
        $this->reporter->announceTestFail();
    }
    
    /**
     * Fired when all threads are reported to be complete
     * This performs the report output for all generated reports up to this
     * point.
     **/
    public function onComplete() {
        $this->reporter->generateReport($this->report_list);
    }
}
