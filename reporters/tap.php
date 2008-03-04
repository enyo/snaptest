<?php

/**
 * Text Output Unit Test Reporter
 */
class Snap_Tap_UnitTestReporter extends Snap_UnitTestReporter {

    protected $type_mapping = array(
        'pass'      => true,
        'case'      => true,
        'fail'      => false,
        'skip'      => true,
        'notimplemented' => false,
        'defect'    => false,
        'phperr'    => false,
    );

    /**
     * generate a text based report of the output data
     * @return void
     */
    public function generateReport($reports) {
        echo "\n";
        echo "TAP version 13\n";
        
        $count = 0;
        if (is_array($reports)) foreach ($reports as $report) {
            if ($report['type'] == 'case' || $report['type'] == 'debug') {
                continue;
            }
            
            $count++;
        }
        
        // number of tests
        echo "1..{$count}\n";
        
        // header information
        echo "#\n";
        echo "# Snap TAP Output Reporter\n";
        echo "# This reporter shows errors after the fact. If planning on\n";
        echo "# streaming this output, testing should be done in as small\n";
        echo "# segments as possible.\n";
        echo "#\n";

        $phperr = 0;
        $i = 1;
        if (is_array($reports)) foreach ($reports as $report) {
            
            // skip case complete
            if ($report['type'] == 'case') {
                continue;
            }
            
            if ($report['type'] == 'debug') {
                $debug = str_replace(array("\r\n", "\r"), "\n", $report['message']);
                $debug = preg_replace('/\n/m', "\n# ", $debug);
                $file = $report['file'];
                echo "# DEBUG:\n# $file\n# $debug\n";
                continue;
            }
            
            if ($report['type'] == 'phperr') {
                $phperr++;
            }
            
            $report_number = $i;
            $i++;

            $function = (isset($report['function'])) ? $report['function'] : 'unknown';
            $classname = (isset($report['class'])) ? $report['class'] : 'unknown';
            $file = (isset($report['file'])) ? $report['file'] : 'unknown';
            $message = (isset($report['message'])) ? $report['message'] : 'unknown';
            
            // normalize pass status
            $pass_status = ($this->type_mapping[$report['type']]) ? 'ok' : 'not ok';
            
            // make pretty classname / function
            $pretty_function = preg_replace('/^test /i', '', preg_replace('/[^A-Z0-9 ]/i', '', preg_replace('/([A-Z])/', ' \\1', $function)));
            
            echo "$pass_status $report_number - $pretty_function\n";
            
            if (!$this->type_mapping[$report['type']]) {
                echo "  ---\n";
                echo "    message: $message\n";
                echo "    severity: fail\n";
                echo "    location:\n";
                echo "      method: $function\n";
                echo "      class:  $classname\n";
                echo "      file:   $file\n";
                echo "  ...\n";
            }
        }
        
        if ($phperr > 0) {
            echo "\n";
            echo '# You have unchecked errors in your tests.  These errors should be'."\n";
            echo '# removed, or acknowledged with $this->willError() in their respective'."\n";
            echo '# tests.';
            echo "\n";
        }
    }
    
    // simulated TAP streaming does not do this
    public function announceTestPass() {}
    public function announceTestFail() {}
    public function announceTestNotImplemented() {}    
    public function announceTestSkip() {}
    public function announceTestDefect() {}
    public function announceTestCaseComplete() {}
}
