<?php

/**
 * Text Output Unit Test Reporter
 */
class Snap_Tap_UnitTestReporter extends Snap_UnitTestReporter {

    /**
     * generate a text based report of the output data
     * @return void
     */
    public function generateReport($reports) {
        echo "\n";
        echo "TAP version 13\n";
        
        // number of tests
        echo "1..{$this->tests}\n";
        
        // header information
        echo "#\n";
        echo "# Snap TAP Output Reporter\n";
        echo "# This reporter shows errors after the fact. If planning on\n";
        echo "# streaming this output, testing should be done in as small\n";
        echo "# segments as possible.\n";
        echo "#\n";
        
        $i = 1;
        if (is_array($reports)) foreach ($reports as $report) {
            $report_number = $i;
            $i++;

            $function = (isset($report['function'])) ? $report['function'] : 'unknown';
            $classname = (isset($report['class'])) ? $report['class'] : 'unknown';
            $file = (isset($report['file'])) ? $report['file'] : 'unknown';
            $message = (isset($report['message'])) ? $report['message'] : 'unknown';
            
            // normalize pass status
            $pass_status = (isset($report['type']) && $report['type'] == 'pass') ? 'ok' : 'not ok';
            
            // make pretty classname / function
            $pretty_function = preg_replace('/^test /i', '', preg_replace('/[^A-Z0-9 ]/i', '', preg_replace('/([A-Z])/', ' \\1', $function)));
            
            echo "$pass_status $report_number - $pretty_function\n";
            
            if ($pass_status != 'ok') {
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
        
        if ($this->php_errors > 0) {
            echo "\n";
            echo '# You have unchecked errors in your tests.  These errors should be'."\n";
            echo '# removed, or acknowledged with $this->willError() in their respective'."\n";
            echo '# tests.';
            echo "\n";
        }
    }
    
    // simulated TAP streaming does not do this
    protected function announceTestPasses($passes, $defects, $tests, $classname) {}
}
