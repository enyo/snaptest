<?php

/**
 * Text Output Unit Test Reporter
 */
class Snap_Tap_UnitTestReporter extends Snap_UnitTestReporter {

    /**
     * generate a text based report of the output data
     * @return void
     */
    public function generateReport() {
        echo "\n";
        echo "TAP version 13\n";
        
        // number of tests
        echo "1..{$this->tests}\n";
        
        // header information
        echo "#\n";
        echo "# Snap TAP Output Reporter\n";
        echo "# Test numbers 1..N are simulated, errors will be\n";
        echo "# in the last N-failures test slots\n";
        echo "#\n";
        
        for ($i = 1; $i <= $this->passes; $i++) {
            echo "ok $i - simulated test\n";
        }
        
        $test_counter = $this->passes + 1;
        
        foreach ($this->reports as $report) {
            $function = (isset($report['function'])) ? $report['function'] : 'unknown';
            $classname = (isset($report['class'])) ? $report['class'] : 'unknown';
            $file = (isset($report['file'])) ? $report['file'] : 'unknown';
            $message = (isset($report['message'])) ? $report['message'] : 'unknown';
            
            echo "not ok $test_counter - $function\n";
            echo "  ---\n";
            echo "    message: $message\n";
            echo "    severity: fail\n";
            echo "    location:\n";
            echo "      method: $function\n";
            echo "      class:  $classname\n";
            echo "      file:   $file\n";
            echo "  ...\n";
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
